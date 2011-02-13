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
    protected $tracer = NULL;

    public $did_run = FALSE;

    public $out1;
    public $out2;
    public $out2_never_true = FALSE;

    public $id = NULL;

    public function __construct(TaskTracer $tracer = NULL)
    {
        $this->tracer = $tracer;
    }

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

    public function Run()
    {
        $this->did_run = TRUE;
        $this->out1 = 'foo';
        $this->out2 = 'bar';
        if ($this->tracer) {
            $this->tracer->Emit($this);
            $this->tracer = NULL;
        }
    }
}

// Add this as a fixture in the test class.
class TaskTracer
{
    protected $traces;

    public function Emit(TestTask $task)
    {
        $this->traces[] = $task;
        print '  TRACE: ' . $this->TaskToString($task) . "\n";
    }

    public function TaskToString($task)
    {
        if ($task === NULL)
            return '(null)';
        $hash = spl_object_hash($task);
        $hash = md5($hash);
        return get_class($task) . ' # ' . substr($hash, strlen($hash) - 7);
    }

    public function GetTraces()
    {
        return $this->traces;
    }
}

// Put one of these in each test function:
//
//  function testSomeThing() {
//    $tracer = new ScopedTaskExpectations($this, $this->fixture);
//    $task = new TestTask($this->fixture);
//    $tracer->AddExpectation($task);
//    $this->pump->QueueTask($task);
//    $this->pump->Loop();
//  }
class ScopedTaskExpectations
{
    protected $test = NULL;
    protected $tracer = NULL;
    protected $expectations = array();

    public function __construct(\PHPUnit_Framework_TestCase $test, TaskTracer $tracer)
    {
        $this->test   = $test;
        $this->tracer = $tracer;
    }

    public function __destruct()
    {
        $actual_traces = $this->tracer->GetTraces();
        foreach ($this->expectations as $index => $expected) {
            $actual = isset($actual_traces[$index]) ? $actual_traces[$index] : NULL;
            if ($expected !== $actual) {
                print "***** TRACE MISMATCH *****\n";
                print "@ $index: Expected: " . $this->tracer->TaskToString($expected) . "\n";
                print "   does not match: " . $this->tracer->TaskToString($actual) . "\n";
            }
            $this->test->assertSame($expected, $actual);
        }
    }

    public function AddExpectation(TestTask $task)
    {
        $this->expectations[] = $task;
    }
}

class TestOutputHandler extends tasks\OutputHandler
{
    public $do_start = FALSE;

    public function Start()
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
