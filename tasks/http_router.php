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

require_once PHALANX_ROOT . '/base/dictionary.php';
require_once PHALANX_ROOT . '/tasks/dynamic_router.php';

class HTTPRouter extends DynamicRouter
{
    // The name of the input key to get the task name from.
    protected $task_input_key;

    // The request method, uppercase.
    protected $request_method;

    // The input parsed from the URL.
    protected $url_input;

    // Create a new HTTPDispatcher that will synthesize tasks based on the
    // task name specified in the HTTP input variable, keyed by
    // |$task_input_key|.
    public function __construct($task_input_key = 'phalanx_task')
    {
        $this->task_input_key = $task_input_key;
    }

    public function VendTask()
    {
        $this->request_method = strtoupper($_SERVER['REQUEST_METHOD']);
        $url = '';
        if (isset($_GET['__dispatch__']))
            $url = $_GET['__dispatch__'];
        $this->url_input = $this->_TokenizeURL($url);
        return parent::VendTask();
    }

    // Getters and setters.
    // ------------------------------------------------------------------------
    public function task_input_key() { return $this->task_input_key; }

    // This splits a request URL into the task name and then appropriate key
    // value matching. URLs can take the form:
    //   /task_name/id
    //   /task_name/k1/v1/k2/v2/
    protected function _TokenizeURL($url)
    {
        $input = new \phalanx\base\Dictionary();
        $parts = explode('/', trim($url, '/'));

        $input->Set('_task', $parts[0]);
        array_shift($parts);

        if (count($parts) == 1)
        {
            $input->Set('_id', $parts[0]);
            return $input;
        }

        for ($i = 0; $i < count($parts); $i += 2)
        {
            if (!isset($parts[$i]) || !isset($parts[$i+1]))
                throw new HTTPDispatcherException("Invalid key-value pair in URL '$url'");
            $input->Set($parts[$i], $parts[$i+1]);
        }
        return $input;
    }

    // Gets the task name.
    protected function _GetTaskName()
    {
        $url_task = $this->url_input->Get('_task');
        if ($url_task != NULL)
            return $url_task;
        if ($this->request_method == 'POST')
            if (isset($_POST[$this->task_input_key]))
                return $_POST[$this->task_input_key];
        return '';
    }

    // Returns the input based on the keys provided.
    protected function _GetInput(Array $keys)
    {
        $input = new \phalanx\base\Dictionary();
        $input->_method = $this->request_method;
        if ($this->request_method == 'GET')
        {
            foreach ($keys as $key)
                if ($this->url_input->HasKey($key))
                    $input->Set($key, $this->url_input->Get($key));
            return $input;
        }
        else if ($this->request_method == 'POST')
        {
            foreach ($keys as $key)
                if (isset($_POST[$key]))
                    $input->Set($key, $_POST[$key]);
            return $input;
        }
        else
        {
            throw new HTTPDispatcherException('Unknown request method "' . $this->request_method . '"');
        }
        return $input;
    }
}

class HTTPDispatcherException extends \Exception
{}
