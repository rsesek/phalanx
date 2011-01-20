<?php
// Phalanx
// Copyright (c) 2009-2010 Blue Static
// 
// This program is free software: you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by the Free
// Software Foundation, either version 3 of the License, or any later version.
// 
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
// FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
// more details.
//
// You should have received a copy of the GNU General Public License along with
// this program.  If not, see <http://www.gnu.org/licenses/>.

namespace phalanx\tasks;

require_once PHALANX_ROOT . '/tasks/input_filter.php';

class HTTPInputFilter implements InputFilter
{
    // The name of the input key to get the task name from.
    protected $action_key;

    // The request method, uppercase.
    protected $request_method;

    // The input parsed from the URL.
    protected $url_input;

    // Create a new HTTPInputFilter that will synthesize requests based on the
    // action name specified in the HTTP input variable, keyed by
    // |$action_key|.
    public function __construct($action_key = 'action')
    {
        $this->action_key = $action_key;
    }

    public function CreateRequest()
    {
        $this->request_method = strtoupper($_SERVER['REQUEST_METHOD']);
        $url = '';
        if (isset($_GET['__dispatch__']))
            $url = $_GET['__dispatch__'];
        $this->url_input = $this->_TokenizeURL($url);

        $request = new Request($this);
        $request->action = $this->_GetActionName();
        $request->data = $this->_GetInput();
        return $request;
    }

    // Getters and setters.
    // ------------------------------------------------------------------------
    public function action_key() { return $this->action_key; }

    // This splits a request URL into the action name and then appropriate key
    // value matching. URLs can take the form:
    //   /action/id
    //   /action/k1/v1/k2/v2/
    protected function _TokenizeURL($url)
    {
        $input = new \phalanx\base\Dictionary();
        $parts = explode('/', trim($url, '/'));

        $input->Set('_action', $parts[0]);
        array_shift($parts);

        if (count($parts) == 1) {
            $input->Set('_id', $parts[0]);
            return $input;
        }

        for ($i = 0; $i < count($parts); $i += 2) {
            if (!isset($parts[$i]) || !isset($parts[$i+1]))
                throw new HTTPInputFilterException("Invalid key-value pair in URL '$url'");
            $input->Set($parts[$i], $parts[$i+1]);
        }
        return $input;
    }

    // Gets the action name.
    protected function _GetActionName()
    {
        $url_task = $this->url_input->Get('_action');
        if ($url_task != NULL)
            return $url_task;
        if ($this->request_method == 'POST')
            if (isset($_POST[$this->action_key]))
                return $_POST[$this->action_key];
        return '';
    }

    // Returns the input based on the keys provided.
    protected function _GetInput()
    {
        if ($this->request_method == 'GET')
            return $this->url_input;

        if ($this->request_method == 'POST')
            return new \phalanx\base\Dictionary($_POST);

        throw new HTTPInputFilterException('Unknown request method "' . $this->request_method . '"');
    }
}

class HTTPInputFilterException extends \Exception
{}
