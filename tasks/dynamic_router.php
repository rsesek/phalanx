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
    // class name. This is then instantiated.
    protected $task_loader = NULL;

    // This will begin synthesizing tasks and sending them to the pump.
    public function VendTask()
    {
        $task_name  = $this->_GetTaskName();
        if (!$task_name)
            throw new RouterException('Could not determine task name');
        $loader     = $this->task_loader;
        $task_class = $loader($task_name);            
        $input      = $this->_GetInput($task_class::InputList());
        $task       = new $task_class($input);
        return $task;
    }

    // Extracts the task name, to be processed via |$task_loader| from the
    // input keys. Returns the task name (not class name) as a string.
    abstract protected function _GetTaskName();

    // Called by Start(). This should return a Dictionary of input that is to
    // be passed to the task. This function should gather input for the keys
    // passed to it.
    abstract protected function _GetInput(Array $keys);

    // Getters and setters.
    public function set_task_loader(\Closure $loader) { $this->task_loader = $loader; }
    public function task_loader() { return $this->task_loader; }
}
