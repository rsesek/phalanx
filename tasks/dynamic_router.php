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

require_once PHALANX_ROOT . '/tasks/router.php';

// The Dispatcher synthesizes Task objects and puts them into the TaskPump.
abstract class DynamicRouter implements Router
{
    // A lambda that takes a task name and converts it to a fully qualified
    // class name. This is then instantiated. The signature of the function is:
    //    function(Dictionary $input)  ->  (string|NULL)
    protected $task_loader = NULL;

    // Constructor. Set the |$this->task_loader| to an anonymous function
    // Closure.
    public function __construct()
    {
    }

    // This will begin synthesizing tasks and sending them to the pump.
    public function VendTask(Request $input)
    {
        $loader     = $this->task_loader;
        $task_class = $loader($input->action);      
        if (!$task_class)
            return NULL;
        $task       = new $task_class($input);
        return $task;
    }

    // Getters and setters.
    public function set_task_loader(\Closure $loader) { $this->task_loader = $loader; }
    public function task_loader() { return $this->task_loader; }
}
