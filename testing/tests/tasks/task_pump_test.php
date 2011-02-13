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

    public function Run()
    {
        global $test;
        $count = $test->pump->GetDeferredTasks()->Count();
        $test->pump->QueueTask($this->inner_task);
        $test->assertEquals($count+1, $test->pump->GetDeferredTasks()->Count());
        parent::Run();
    }
}

class PreemptedTask extends TestTask
{
    public $inner_task;

    public function Run()
    {
        global $test;
        parent::Run();
        $test->pump->RunTask($this->inner_task);
    }
}

class CancelledTask extends TestTask
{
    public function Run()
    {
        global $test;
        $test->pump->Cancel($this);
    }
}

class PreemptedCancelledTask extends CancelledTask
{
    public $inner_task;

    public function Run()
    {
        global $test;
        parent::Run();
        $test->pump->RunTask($this->inner_task);
    }
}

class CurrentTaskTester extends TestTask
{
    public $inner_task;

    public function Run()
    {
        global $test;
        parent::Run();
        $test->assertSame($this, $test->pump->GetCurrentTask());
        if ($this->inner_task)
            $test->pump->RunTask($this->inner_task);
    }
}

class QuitTask extends TestTask
{
    public function Run()
    {
        global $test;
        parent::Run();
        $test->pump->Quit();
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
        $this->pump->Loop();
    }

    public function testQueueTask()
    {
        $task = new TestTask();

        $this->assertEquals(0, $this->pump->GetTasks()->Count());

        $this->pump->QueueTask($task);
        $this->pump->Loop();

        $this->assertEquals(1, $this->pump->GetTasks()->Count());
        $this->assertTrue($task->did_run);
    }

    public function testRunTask()
    {
        $task = new TestTask();

        $this->assertEquals(0, $this->pump->GetTasks()->Count());

        $this->pump->RunTask($task);
        $this->pump->Loop();

        $this->assertEquals(1, $this->pump->GetTasks()->Count());
        $this->assertTrue($task->did_run);
    }

    public function testRunTaskPreempted()
    {
        $task       = new PreemptedTask();
        $inner_task = new TestTask();
        $task->inner_task = $inner_task;

        $this->assertEquals(0, $this->pump->GetTasks()->Count());

        $this->pump->QueueTask($task);
        $this->pump->Loop();

        $this->assertEquals(2, $this->pump->GetTasks()->Count());

        $this->assertTrue($task->did_run);
        $this->assertTrue($inner_task->did_run);

        $this->assertSame($inner_task, $this->pump->GetTasks()->Top());
        $this->assertSame($task, $this->pump->GetTasks()->Bottom());
    }

    public function testCancel()
    {
        $task = new CancelledTask();

        $this->pump->QueueTask($task);
        $this->pump->Loop();
        $this->assertEquals(1, $this->pump->GetTasks()->Count());

        $this->assertFalse($task->did_run);
        $this->assertTrue($task->is_cancelled());
    }

    public function testPreemptAndCancel()
    {
        $task       = new PreemptedCancelledTask();
        $inner_task = new TestTask();
        $task->inner_task = $inner_task;

        $this->assertEquals(0, $this->pump->GetTasks()->Count());
        $this->pump->QueueTask($task);
        $this->pump->Loop();
        $this->assertEquals(2, $this->pump->GetTasks()->Count());

        $this->assertFalse($task->did_run);
        $this->assertTrue($task->is_cancelled());

        $this->assertTrue($inner_task->did_run);
        $this->assertFalse($inner_task->is_cancelled());
    }

    public function testDeferredWork()
    {
        $task       = new NestedTask();
        $inner_task = new TestTask();
        $task->inner_task = $inner_task;

        $this->assertEquals(0, $this->pump->GetDeferredTasks()->Count());
        $this->pump->QueueTask($task);
        $this->pump->Loop();
        $this->assertEquals(0, $this->pump->GetDeferredTasks()->Count());
    }

    public function testGetTasks()
    {
        $task = new TestTask();
        $this->pump->QueueTask($task);
        $this->pump->Loop();
        $this->assertEquals(1, $this->pump->GetTasks()->Count());
        $this->assertSame($task, $this->pump->GetTasks()->Top());
    }

    public function testGetLongerTaskChain()
    {
        $task1 = new TestTask();
        $task1->name = 'first';
        $task2 = new TestTask();
        $task2->name = 'second';
        $this->pump->QueueTask($task1);
        $this->pump->QueueTask($task2);
        $this->pump->Loop();
        $this->assertEquals(2, $this->pump->GetTasks()->Count());
        $this->assertSame($task2, $this->pump->GetTasks()->Top());
        $this->assertSame($task1, $this->pump->GetTasks()->Bottom());
    }

    public function testQuitTask()
    {
        $task1 = new TestTask();
        $task2 = new QuitTask();
        $task3 = new TestTask();

        $this->pump->QueueTask($task1);
        $this->pump->QueueTask($task2);
        $this->pump->QueueTask($task3);
        $this->pump->Loop();

        $chain = $this->pump->GetTasks();
        $this->assertEquals(2, $chain->Count());
        $this->assertTrue($task1->did_run);
        $this->assertTrue($task2->did_run);
        $this->assertFalse($task3->did_run);
    }

    public function testNestedRunTasks()
    {
        $task = new NestedTask();
        $task->inner_task = new PreemptedTask();
        $task->inner_task->inner_task = new PreemptedTask();
        $task->inner_task->inner_task->inner_task = new TestTask();

        $this->pump->QueueTask($task);
        $this->pump->Loop();

        $chain = $this->pump->GetTasks();
        $this->assertEquals(4, $chain->Count());
        $this->assertSame($task, $chain->Bottom());
        $this->assertSame($task->inner_task, $chain->OffsetGet(2));
        $this->assertSame($task->inner_task->inner_task, $chain->OffsetGet(1));
        $this->assertSame($task->inner_task->inner_task->inner_task, $chain->Top());
    }

    public function testDontRunCancelledTasks()
    {
        $task1 = new TestTask();
        $task2 = new TestTask();
        $task3 = new TestTask();

        $this->pump->QueueTask($task1);
        $this->pump->QueueTask($task2);
        $this->pump->QueueTask($task3);

        $task2->Cancel();

        $this->pump->Loop();

        $this->assertEquals(2, $this->pump->GetTasks()->Count());
        $this->assertTrue($task1->did_run);
        $this->assertFalse($task2->did_run);
        $this->assertTrue($task3->did_run);
    }

    public function testLoopRestart()
    {
        $task1 = new TestTask();
        $task2 = new QuitTask();
        $task3 = new TestTask();

        $this->pump->QueueTask($task1);
        $this->pump->QueueTask($task2);
        $this->pump->QueueTask($task3);

        $this->pump->Loop();  // Pump tasks 1 and 2.

        $this->assertEquals(2, $this->pump->GetTasks()->Count());
        $this->assertTrue($task1->did_run);
        $this->assertTrue($task2->did_run);
        $this->assertFalse($task3->did_run);

        $this->pump->Loop();  // Pump task 3.

        $this->assertEquals(3, $this->pump->GetTasks()->Count());
        $this->assertTrue($task3->did_run);
        $this->assertSame($task3, $this->pump->GetTasks()->Top());
    }
}
