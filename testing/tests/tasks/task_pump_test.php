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

class RunThreeTask extends TestTask
{
    public $inner_task1 = NULL;
    public $inner_task2 = NULL;
    public $inner_task3 = NULL;

    public function Run()
    {
        global $test;
        parent::Run();
        $test->pump->RunTask($this->inner_task1);
        $test->pump->RunTask($this->inner_task2);
        $test->pump->RunTask($this->inner_task3);
    }
}

class TaskPumpTest extends \PHPUnit_Framework_TestCase
{
    public $pump;

    public $fixture;

    public function setUp()
    {
        global $test;
        $test = $this;
        $this->pump = new TaskPump();
        $this->inner_task = NULL;
        $this->fixture = new TaskTracer();
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
        $task = new CurrentTaskTester($this->fixture);
        $task->name = 'first';
        $this->pump->QueueTask($task);

        $task = new CurrentTaskTester($this->fixture);
        $task->name = 'outer';
        $task->inner_task = new CurrentTaskTester($this->fixture);
        $task->inner_task->name = 'inner';
        $this->pump->QueueTask($task);
        $this->pump->Loop();
    }

    public function testQueueTask()
    {
        $task = new TestTask($this->fixture);

        $this->assertEquals(0, $this->pump->GetTasks()->Count());

        $this->pump->QueueTask($task);
        $this->pump->Loop();

        $this->assertEquals(1, $this->pump->GetTasks()->Count());
        $this->assertTrue($task->did_run);
    }

    public function testRunTask()
    {
        $task = new TestTask($this->fixture);

        $this->assertEquals(0, $this->pump->GetTasks()->Count());

        $this->pump->RunTask($task);
        $this->pump->Loop();

        $this->assertEquals(1, $this->pump->GetTasks()->Count());
        $this->assertTrue($task->did_run);
    }

    public function testRunTaskPreempted()
    {
        $task       = new PreemptedTask($this->fixture);
        $inner_task = new TestTask($this->fixture);
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
        $task = new CancelledTask($this->fixture);

        $this->pump->QueueTask($task);
        $this->pump->Loop();
        $this->assertEquals(1, $this->pump->GetTasks()->Count());

        $this->assertFalse($task->did_run);
        $this->assertTrue($task->is_cancelled());
    }

    public function testPreemptAndCancel()
    {
        $task       = new PreemptedCancelledTask($this->fixture);
        $inner_task = new TestTask($this->fixture);
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
        $task       = new NestedTask($this->fixture);
        $inner_task = new TestTask($this->fixture);
        $task->inner_task = $inner_task;

        $this->assertEquals(0, $this->pump->GetDeferredTasks()->Count());
        $this->pump->QueueTask($task);
        $this->pump->Loop();
        $this->assertEquals(0, $this->pump->GetDeferredTasks()->Count());
    }

    public function testGetTasks()
    {
        $task = new TestTask($this->fixture);
        $this->pump->QueueTask($task);
        $this->pump->Loop();
        $this->assertEquals(1, $this->pump->GetTasks()->Count());
        $this->assertSame($task, $this->pump->GetTasks()->Top());
    }

    public function testGetLongerTaskChain()
    {
        $task1 = new TestTask($this->fixture);
        $task1->name = 'first';
        $task2 = new TestTask($this->fixture);
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
        $task1 = new TestTask($this->fixture);
        $task2 = new QuitTask($this->fixture);
        $task3 = new TestTask($this->fixture);

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
        $task = new NestedTask($this->fixture);
        $task->inner_task = new PreemptedTask();
        $task->inner_task->inner_task = new PreemptedTask();
        $task->inner_task->inner_task->inner_task = new TestTask($this->fixture);

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
        $tracer = new ScopedTaskExpectations($this, $this->fixture);

        $task1 = new TestTask($this->fixture);
        $task2 = new TestTask($this->fixture);
        $task3 = new TestTask($this->fixture);

        $tracer->AddExpectation($task1);
        $tracer->AddExpectation($task3);

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
        $task1 = new TestTask($this->fixture);
        $task2 = new QuitTask($this->fixture);
        $task3 = new TestTask($this->fixture);

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

    public function testRunTwoTasksNested()
    {
        $tracer = new ScopedTaskExpectations($this, $this->fixture);

        $task1 = new RunThreeTask($this->fixture);
        $task2 = new TestTask($this->fixture);
        $task3 = new TestTask($this->fixture);
        $task4 = new TestTask($this->fixture);
        $task1->inner_task1 = $task2;
        $task1->inner_task2 = $task3;
        $task1->inner_task3 = $task4;

        $this->pump->QueueTask($task1);
        $this->pump->Loop();

        $tracer->AddExpectation($task1);
        $tracer->AddExpectation($task2);
        $tracer->AddExpectation($task4);
        $tracer->AddExpectation($task3);
    }
}
