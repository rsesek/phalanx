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

namespace phalanx\test;
use \phalanx\tasks as tasks;

// Common includes.
require_once PHALANX_ROOT . '/tasks/cli_dispatcher.php';
require_once PHALANX_ROOT . '/tasks/cli_output_handler.php';
require_once PHALANX_ROOT . '/tasks/dispatcher.php';
require_once PHALANX_ROOT . '/tasks/task.php';
require_once PHALANX_ROOT . '/tasks/task_pump.php';
require_once PHALANX_ROOT . '/tasks/http_dispatcher.php';
require_once PHALANX_ROOT . '/tasks/output_handler.php';
require_once PHALANX_ROOT . '/tasks/unit_test_output_handler.php';
require_once PHALANX_ROOT . '/tasks/view_output_handler.php';

class TestTask extends tasks\Task
{
    public $will_fire = FALSE;
    public $fire = FALSE;
    public $cleanup = FALSE;

    public $out1;
    public $out2;
    public $out2_never_true = FALSE;

    public $id = NULL;

    // The property should hide this from OutputHandler::_GetTaskData().
    public function out2()
    {
        $this->out2_never_true = TRUE;
    }

    public function out3()
    {
        return 'moo';
    }

    static public function InputList()
    {
        return array('key1', 'key2');
    }

    static public function OutputList()
    {
        return array('will_fire', 'fire', 'cleanup', 'out1', 'out2', 'out3', 'no_out', 'id');
    }

    public function WillFire()
    {
        $this->will_fire = TRUE;
        parent::WillFire();  // Boost code coverage. No-op.
    }

    public function Fire()
    {
        $this->fire = TRUE;
        $this->out1 = 'foo';
        $this->out2 = 'bar';
    }

    public function Cleanup()
    {
        $this->cleanup = TRUE;
    }
}

class InitOnlyTask extends TestTask
{
    public function WillFire()
    {
        parent::WillFire();
        $this->Cancel();
    }
}

class TestOutputHandler extends tasks\OutputHandler
{
    public $do_start = FALSE;

    protected function _DoStart()
    {
        $this->do_start = TRUE;
    }

    public function T_GetTaskData(tasks\Task $task)
    {
        // TODO: GetTaskData() is now public. We can remove this method.
        return $this->GetTaskData($task);
    }
}

class TestDispatcher extends tasks\Dispatcher
{
    protected function _GetTaskName()
    {
        return 'task.test';
    }

    protected function _GetInput(Array $input_list)
    {
        $input = new \phalanx\base\Dictionary();
        foreach ($input_list as $key)
            $input->Set($key, 'test:' . $key);
        return $input;
    }
}
