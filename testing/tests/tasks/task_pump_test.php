<?php
// Phalanx
// Copyright (c) 2009 Blue Static
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
use \phalanx\tasks\TaskPump;

require_once TEST_ROOT . '/tests/tasks.php';

$test = NULL;

class NestedTask extends TestTask
{
    public $inner_task;

    public function Fire()
    {
        global $test;
        $count = $test->pump->GetDeferredTasks()->Count();
        $test->pump->QueueTask($this->inner_task);
        $test->assertEquals($count+1, $test->pump->GetDeferredTasks()->Count());
        parent::Fire();
    }
}

class PreemptedTask extends TestTask
{
    public $inner_task;

    public function Fire()
    {
        global $test;
        $test->pump->RunTask($this->inner_task);
        parent::Fire();
    }
}

class CancelledTask extends TestTask
{
    public function Fire()
    {
        global $test;
        $test->pump->Cancel($this);
    }
}

class CancelledWillFireTask extends TestTask
{
    public function WillFire()
    {
        global $test;
        parent::WillFire();
        $test->pump->Cancel($this);
    }
}

class PreemptedCancelledTask extends CancelledTask
{
    public $inner_task;

    public function Fire()
    {
        global $test;
        $test->pump->RunTask($this->inner_task);
        parent::Fire();
    }
}

class CurrentTaskTester extends TestTask
{
    public $inner_task;

    public function WillFire()
    {
        global $test;
        parent::WillFire();
        $test->assertSame($this, $test->pump->GetCurrentTask());
        $test->assertEquals(TaskPump::TASK_WILL_FIRE, $test->pump->GetCurrentTaskState());
    }
    public function Fire()
    {
        global $test;
        parent::Fire();
        $test->assertSame($this, $test->pump->GetCurrentTask());
        $test->assertEquals(TaskPump::TASK_FIRE, $test->pump->GetCurrentTaskState());
        if ($this->inner_task)
            $test->pump->RunTask($this->inner_task);
    }
    public function Cleanup()
    {
        global $test;
        parent::CleanUp();
        $test->assertSame($this, $test->pump->GetCurrentTask());
        $test->assertEquals(TaskPump::TASK_CLEANUP, $test->pump->GetCurrentTaskState());
    }
}

class CancelDeferredTasksTester extends TestTask
{
    public function Fire()
    {
        global $test;
        $test->pump->CancelDeferredTasks();
        parent::Fire();
    }
}

class StopPumpTask extends TestTask
{
    public function Fire()
    {
        global $test;
        parent::Fire();
        $test->pump->StopPump();
    }
}

class TaskPumpTest extends \PHPUnit_Framework_TestCase
{
    public $pump;

    public function setUp()
    {
        global $test;
        $test = $this;
        $this->pump = new TaskPump();
        $this->inner_task = NULL;
    }

    public function testSharedPump()
    {
        // Reset.
        TaskPump::T_set_pump(NULL);

        $this->assertNotNull(TaskPump::Pump(), 'Did not create shared pump.');
        $this->assertNotSame($this->pump, TaskPump::Pump());

        TaskPump::set_pump($this->pump);
        $this->assertSame($this->pump, TaskPump::Pump());
    }

    public function testGetCurrentTask()
    {
        $task = new CurrentTaskTester();
        $task->name = 'first';
        $this->pump->QueueTask($task);

        $task = new CurrentTaskTester();
        $task->name = 'outer';
        $task->inner_task = new CurrentTaskTester();
        $task->inner_task->name = 'inner';
        $this->pump->QueueTask($task);
    }

    public function testSetOutputHandler()
    {
        $this->assertNull($this->pump->output_handler());

        $handler = new TestOutputHandler();
        $this->pump->set_output_handler($handler);
        $this->assertSame($handler, $this->pump->output_handler());
    }

    public function testQueueTask()
    {
        $task = new TestTask();

        $this->assertEquals(0, $this->pump->GetTaskHistory()->Count());
        $this->pump->QueueTask($task);
        $this->assertEquals(1, $this->pump->GetTaskHistory()->Count());

        $this->assertTrue($task->will_fire);
        $this->assertTrue($task->fire);
        $this->assertTrue($task->cleanup);
    }

    public function testRunTask()
    {
        $task = new TestTask();

        $this->assertEquals(0, $this->pump->GetTaskHistory()->Count());
        $this->pump->RunTask($task);
        $this->assertEquals(1, $this->pump->GetTaskHistory()->Count());

        $this->assertTrue($task->will_fire);
        $this->assertTrue($task->fire);
        $this->assertTrue($task->cleanup);
    }

    public function testRunTaskPreempted()
    {
        $task       = new PreemptedTask();
        $inner_task = new TestTask();
        $task->inner_task = $inner_task;

        $this->assertEquals(0, $this->pump->GetTaskHistory()->Count());
        $this->pump->QueueTask($task);
        $this->assertEquals(2, $this->pump->GetTaskHistory()->Count());

        $this->assertTrue($task->will_fire);
        $this->assertTrue($task->fire);
        $this->assertTrue($task->cleanup);

        $this->assertTrue($inner_task->will_fire);
        $this->assertTrue($inner_task->fire);
        $this->assertTrue($inner_task->cleanup);

        $this->assertSame($task, $this->pump->GetTaskHistory()->Top());
        $this->assertSame($inner_task, $this->pump->GetTaskHistory()->Bottom());
    }

    public function testCancel()
    {
        $task = new CancelledTask();

        $this->pump->QueueTask($task);
        $this->assertEquals(0, $this->pump->GetTaskHistory()->Count());

        $this->assertTrue($task->will_fire);
        $this->assertFalse($task->fire);
        $this->assertTrue($task->cleanup);
        $this->assertTrue($task->is_cancelled());
    }

    public function testCancelInWillFire()
    {
        $task = new CancelledWillFireTask();

        $this->pump->QueueTask($task);
        $this->assertEquals(0, $this->pump->GetTaskHistory()->Count());

        $this->assertTrue($task->will_fire);
        $this->assertFalse($task->fire);
        $this->assertTrue($task->cleanup);
        $this->assertTrue($task->is_cancelled());
    }

    public function testPreemptAndCancel()
    {
        $task       = new PreemptedCancelledTask();
        $inner_task = new TestTask();
        $task->inner_task = $inner_task;

        $this->assertEquals(0, $this->pump->GetTaskHistory()->Count());
        $this->pump->QueueTask($task);
        $this->assertEquals(1, $this->pump->GetTaskHistory()->Count());

        $this->assertTrue($task->will_fire);
        $this->assertFalse($task->fire);
        $this->assertTrue($task->cleanup);
        $this->assertTrue($task->is_cancelled());

        $this->assertTrue($inner_task->will_fire);
        $this->assertTrue($inner_task->fire);
        $this->assertTrue($inner_task->cleanup);
        $this->assertFalse($inner_task->is_cancelled());
    }

    public function testDeferredWork()
    {
        $task       = new NestedTask();
        $inner_task = new TestTask();
        $task->inner_task = $inner_task;

        $this->assertEquals(0, $this->pump->GetDeferredTasks()->Count());
        $this->pump->QueueTask($task);
        $this->assertEquals(0, $this->pump->GetDeferredTasks()->Count());
    }

    public function testCancelDeferredTasks()
    {
        $this->pump->GetDeferredTasks()->Push(new TestTask());
        $this->pump->GetDeferredTasks()->Push(new TestTask());
        $this->pump->GetDeferredTasks()->Push(new TestTask());

        $this->assertEquals(3, $this->pump->GetDeferredTasks()->Count());
        $this->pump->CancelDeferredTasks();
        $this->assertEquals(0, $this->pump->GetDeferredTasks()->Count());
    }

    public function testStopPump()
    {
        $this->pump = $this->getMock('phalanx\tasks\TaskPump', array('_Exit'));
        $this->pump->expects($this->once())->method('_Exit');

        $output_handler = $this->getMock('phalanx\test\TestOutputHandler');
        $output_handler->expects($this->once())->method('Start');
        $this->pump->set_output_handler($output_handler);

        $task = new StopPumpTask();
        $this->pump->QueueTask($task);

        $this->assertTrue($task->will_fire);
        $this->assertTrue($task->fire);
        $this->assertTrue($task->cleanup);
    }

    public function testStopPumpNoCurrentTask()
    {
        $this->pump = $this->getMock('phalanx\tasks\TaskPump', array('_Exit'));
        $this->pump->expects($this->once())->method('_Exit');

        $output_handler = $this->getMock('phalanx\test\TestOutputHandler');
        $output_handler->expects($this->once())->method('Start');
        $this->pump->set_output_handler($output_handler);

        $this->pump->StopPump();
    }

    public function testNestedStopPump()
    {
        $this->pump = $this->getMock('phalanx\tasks\TaskPump', array('_Exit'));

        $output_handler = $this->getMock('phalanx\test\TestOutputHandler');
        $output_handler->expects($this->once())->method('Start');
        $this->pump->set_output_handler($output_handler);

        $task = new PreemptedTask();
        $task->inner_task = new StopPumpTask();

        $test = $this;
        $this->pump->expects($this->once())
                   ->method('_Exit')
                   ->will($this->returnCallback(function() use ($test, $task) {
            $test->assertEquals(0, $test->pump->GetTaskHistory()->Count());
            $test->assertSame($task, $test->pump->GetAllTasks()->Bottom());
            $test->assertSame($task->inner_task, $test->pump->GetAllTasks()->Top());
        }));

        $this->pump->QueueTask($task);
    }

    public function testTerminate()
    {
        $this->pump = $this->getMock('phalanx\tasks\TaskPump', array('_Exit'));
        $msg = 'testing 1 2 3';

        ob_start();
        $this->pump->expects($this->once())->method('_Exit');
        $this->pump->Terminate($msg);
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($msg, $result);
    }

    public function testGetTaskHistory()
    {
        $task = new TestTask();
        $this->pump->QueueTask($task);
        $this->assertEquals(1, $this->pump->GetTaskHistory()->Count());
        $this->assertSame($task, $this->pump->GetTaskHistory()->Top());
    }

    public function testGetLongerTaskChain()
    {
        $task1 = new TestTask();
        $task1->name = 'first';
        $task2 = new TestTask();
        $task2->name = 'second';
        $this->pump->QueueTask($task1);
        $this->pump->QueueTask($task2);
        $this->assertEquals(2, $this->pump->GetTaskHistory()->Count());
        $this->assertSame($task2, $this->pump->GetTaskHistory()->Top());
        $this->assertSame($task1, $this->pump->GetTaskHistory()->Bottom());
    }

    public function testCancelDeferredTasksByRaisingAnTask()
    {
        $task = new CancelDeferredTasksTester();
        $this->pump->RunTask($task);
    }
}
