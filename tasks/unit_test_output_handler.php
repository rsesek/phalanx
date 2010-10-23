<?php
// Phalanx
// Copyright (c) 2010 Blue Static
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

require_once PHALANX_ROOT . '/tasks/task_pump.php';
require_once PHALANX_ROOT . '/tasks/output_handler.php';

// This OutputHandler implementation can be used by application developers to
// test their own tasks. This OutputHandler does nothing but collect the
// output from ALL tasks in the chain and stores it.
class UnitTestOutputHandler extends OutputHandler
{
    // The function that transforms an task name into a template name. The
    // array is indexed by ints, with 0 being the top of the task chain stack
    // and N being the bottom (oldest).
    protected $task_data = array();

    protected function _DoStart()
    {
        $task_chain = TaskPump::Pump()->GetTaskHistory();
        foreach ($task_chain as $task)
            array_push($this->task_data, $this->GetTaskData($task));
    }

    // Returns an array of all task data in the same order of tasks as the
    // TaskPump's task chain. The values are base\PropertyBags.
    public function task_data() { return $this->task_data; }
}
