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

require_once PHALANX_ROOT . '/tasks/request.php';
require_once PHALANX_ROOT . '/tasks/router.php';

// A StaticRouter maps the _action input key to a Task vendor.
abstract class StaticRouter implements Router
{
    // A map of _action input keys to either a:
    //  1. String name of a Task class to instantiate
    //  2. A Closure to run that returns an instantiated Task
    protected $action_map = array();

    // Constructor. Subclasses should set up their action maps here.
    public function __construct()
    {
    }

    // Called before an instance of the |$class_name| Task is instantiated. This
    // is the point at which clients should require the file for their class, if
    // it isn't already loaded or registered with autoload.
    protected function _WillLoadTask($class_name)
    {}

    // This will begin synthesizing tasks and sending them to the pump.
    public function VendTask(Request $input)
    {
        $action = $input->action;
        if (!isset($this->action_map[$action]))
            return NULL;
        $task_vendor = $this->action_map[$action];
        $task        = NULL;
        if ($task_vendor instanceof \Closure) {
            $task = $task_vendor($input);
        } else {
            $this->_WillLoadTask($task_vendor);
            $task = new $task_vendor($input);
        }

        // Return the Task if the Router produced one.
        if ($task)
            return $task;

        // If there isn't a Task but the route was registered, an error
        // occurred.
        throw new RouterException('Error vending task for action "' . $action . '"');
    }
}
