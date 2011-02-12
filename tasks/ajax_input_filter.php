<?php
// Phalanx
// Copyright (c) 2011 Blue Static
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

class AJAXInputFilter implements InputFilter
{
    // The name of the input key to get the task name from.
    protected $action;

    // The request method, uppercase.
    protected $request_method;

    public function CreateRequest()
    {
        $request_method = strtoupper($_SERVER['REQUEST_METHOD']);
        $action         = $_SERVER['REQUEST_URI'];

        $request = new Request($this);
        $request->action = "{$this->request_method}:{$this->action}";
        $request->data   = $this->_GetInput();
        return $request;
    }

    // Returns the input based on the keys provided.
    protected function _GetInput()
    {
        switch ($this->request_method) {
            case 'GET':
                return new \phalanx\based\Dictionary($_GET);
            case 'POST':
                return new \phalanx\based\Dictionary($_POST);
            default:
                throw new HTTPDispatcherException('Unknown request method "' . $this->request_method . '"');
        }
    }

    static public function EvaluateContext()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
