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
use \phalanx\events as events;
use \phalanx\events\EventPump;

require_once 'PHPUnit/Framework.php';

$test = NULL;

class NestedEvent extends TestEvent
{
    public $inner_event;

    public function Fire()
    {
        global $test;
        $count = $test->pump->GetDeferredEvents()->Count();
        $test->pump->PostEvent($this->inner_event);
        $test->assertEquals($count+1, $test->pump->GetDeferredEvents()->Count());
        parent::Fire();
    }
}

class PreemptedEvent extends TestEvent
{
    public $inner_event;

    public function Fire()
    {
        global $test;
        $test->pump->RaiseEvent($this->inner_event);
        parent::Fire();
    }
}

class CancelledEvent extends TestEvent
{
    public function Fire()
    {
        global $test;
        $test->pump->Cancel($this);
    }
}

class CancelledWillFireEvent extends TestEvent
{
    public function WillFire()
    {
        global $test;
        parent::WillFire();
        $test->pump->Cancel($this);
    }
}

class PreemptedCancelledEvent extends CancelledEvent
{
    public $inner_event;

    public function Fire()
    {
        global $test;
        $test->pump->RaiseEvent($this->inner_event);
        parent::Fire();
    }
}

class CurrentEventTester extends TestEvent
{
    public $inner_event;

    public function WillFire()
    {
        global $test;
        parent::WillFire();
        $test->assertSame($this, $test->pump->GetCurrentEvent());
        $test->assertEquals(EventPump::EVENT_WILL_FIRE, $test->pump->GetCurrentEventState());
    }
    public function Fire()
    {
        global $test;
        parent::Fire();
        $test->assertSame($this, $test->pump->GetCurrentEvent());
        $test->assertEquals(EventPump::EVENT_FIRE, $test->pump->GetCurrentEventState());
        if ($this->inner_event)
            $test->pump->RaiseEvent($this->inner_event);
    }
    public function Cleanup()
    {
        global $test;
        parent::CleanUp();
        $test->assertSame($this, $test->pump->GetCurrentEvent());
        $test->assertEquals(EventPump::EVENT_CLEANUP, $test->pump->GetCurrentEventState());
    }
}

class StopPumpEvent extends TestEvent
{
    public function Fire()
    {
        global $test;
        parent::Fire();
        $test->pump->StopPump();
    }
}

class EventPumpTest extends \PHPUnit_Framework_TestCase
{
    public $pump;

    public function setUp()
    {
        global $test;
        $test = $this;
        $this->pump = new EventPump();
        $this->inner_event = NULL;
    }

    public function testSharedPump()
    {
        // Reset.
        EventPump::T_set_pump(NULL);

        $this->assertNotNull(EventPump::Pump(), 'Did not create shared pump.');
        $this->assertNotSame($this->pump, EventPump::Pump());

        EventPump::set_pump($this->pump);
        $this->assertSame($this->pump, EventPump::Pump());
    }

    public function testGetCurrentEvent()
    {
        $event = new CurrentEventTester();
        $event->name = 'first';
        $this->pump->PostEvent($event);

        $event = new CurrentEventTester();
        $event->name = 'outer';
        $event->inner_event = new CurrentEventTester();
        $event->inner_event->name = 'inner';
        $this->pump->PostEvent($event);
    }

    public function testSetOutputHandler()
    {
        $this->assertNull($this->pump->output_handler());

        $handler = new TestOutputHandler();
        $this->pump->set_output_handler($handler);
        $this->assertSame($handler, $this->pump->output_handler());
    }

    public function testPostEvent()
    {
        $event = new TestEvent();

        $this->assertEquals(0, $this->pump->GetEventChain()->Count());
        $this->pump->PostEvent($event);
        $this->assertEquals(1, $this->pump->GetEventChain()->Count());

        $this->assertTrue($event->will_fire);
        $this->assertTrue($event->fire);
        $this->assertTrue($event->cleanup);
    }

    public function testRaiseEvent()
    {
        $event = new TestEvent();

        $this->assertEquals(0, $this->pump->GetEventChain()->Count());
        $this->pump->RaiseEvent($event);
        $this->assertEquals(1, $this->pump->GetEventChain()->Count());

        $this->assertTrue($event->will_fire);
        $this->assertTrue($event->fire);
        $this->assertTrue($event->cleanup);
    }

    public function testRaiseEventPreempted()
    {
        $event       = new PreemptedEvent();
        $inner_event = new TestEvent();
        $event->inner_event = $inner_event;

        $this->assertEquals(0, $this->pump->GetEventChain()->Count());
        $this->pump->PostEvent($event);
        $this->assertEquals(2, $this->pump->GetEventChain()->Count());

        $this->assertTrue($event->will_fire);
        $this->assertTrue($event->fire);
        $this->assertTrue($event->cleanup);

        $this->assertTrue($inner_event->will_fire);
        $this->assertTrue($inner_event->fire);
        $this->assertTrue($inner_event->cleanup);

        $this->assertSame($event, $this->pump->GetEventChain()->Top());
        $this->assertSame($inner_event, $this->pump->GetEventChain()->Bottom());
    }

    public function testCancel()
    {
        $event = new CancelledEvent();

        $this->pump->PostEvent($event);
        $this->assertEquals(0, $this->pump->GetEventChain()->Count());

        $this->assertTrue($event->will_fire);
        $this->assertFalse($event->fire);
        $this->assertTrue($event->cleanup);
        $this->assertTrue($event->is_cancelled());
    }

    public function testCancelInWillFire()
    {
        $event = new CancelledWillFireEvent();

        $this->pump->PostEvent($event);
        $this->assertEquals(0, $this->pump->GetEventChain()->Count());

        $this->assertTrue($event->will_fire);
        $this->assertFalse($event->fire);
        $this->assertTrue($event->cleanup);
        $this->assertTrue($event->is_cancelled());
    }

    public function testPreemptAndCancel()
    {
        $event       = new PreemptedCancelledEvent();
        $inner_event = new TestEvent();
        $event->inner_event = $inner_event;

        $this->assertEquals(0, $this->pump->GetEventChain()->Count());
        $this->pump->PostEvent($event);
        $this->assertEquals(1, $this->pump->GetEventChain()->Count());

        $this->assertTrue($event->will_fire);
        $this->assertFalse($event->fire);
        $this->assertTrue($event->cleanup);
        $this->assertTrue($event->is_cancelled());

        $this->assertTrue($inner_event->will_fire);
        $this->assertTrue($inner_event->fire);
        $this->assertTrue($inner_event->cleanup);
        $this->assertFalse($inner_event->is_cancelled());
    }

    public function testDeferredWork()
    {
        $event       = new NestedEvent();
        $inner_event = new TestEvent();
        $event->inner_event = $inner_event;

        $this->assertEquals(0, $this->pump->GetDeferredEvents()->Count());
        $this->pump->PostEvent($event);
        $this->assertEquals(0, $this->pump->GetDeferredEvents()->Count());
    }

    public function testCancelDeferredEvents()
    {
        $this->pump->GetDeferredEvents()->Push(new TestEvent());
        $this->pump->GetDeferredEvents()->Push(new TestEvent());
        $this->pump->GetDeferredEvents()->Push(new TestEvent());

        $this->assertEquals(3, $this->pump->GetDeferredEvents()->Count());
        $this->pump->CancelDeferredEvents();
        $this->assertEquals(0, $this->pump->GetDeferredEvents()->Count());
    }

    public function testStopPump()
    {
        $this->pump = $this->getMock('phalanx\events\EventPump', array('_Exit'));
        $this->pump->expects($this->once())->method('_Exit');

        $output_handler = $this->getMock('phalanx\test\TestOutputHandler');
        $output_handler->expects($this->once())->method('Start');
        $this->pump->set_output_handler($output_handler);

        $event = new StopPumpEvent();
        $this->pump->PostEvent($event);

        $this->assertTrue($event->will_fire);
        $this->assertTrue($event->fire);
        $this->assertTrue($event->cleanup);
    }

    public function testStopPumpNoCurrentEvent()
    {
        $this->pump = $this->getMock('phalanx\events\EventPump', array('_Exit'));
        $this->pump->expects($this->once())->method('_Exit');

        $output_handler = $this->getMock('phalanx\test\TestOutputHandler');
        $output_handler->expects($this->once())->method('Start');
        $this->pump->set_output_handler($output_handler);

        $this->pump->StopPump();
    }

    public function testNestedStopPump()
    {
        // This test needs to run in a separate process. When StopPump() gets
        // called, it won't stop execution so the outer event will continue
        // being processed, resulting in an invalid event chain.
        $this->markTestSkipped();

        $this->pump = $this->getMock('phalanx\events\EventPump', array('_Exit'));
        $this->pump->expects($this->once())->method('_Exit');

        $output_handler = $this->getMock('phalanx\test\TestOutputHandler');
        $output_handler->expects($this->once())->method('Start');
        $this->pump->set_output_handler($output_handler);

        $event = new PreemptedEvent();
        $event->inner_event = new StopPumpEvent();
        $this->pump->PostEvent($event);

        $this->assertEquals(2, $this->pump->GetEventChain()->Count());
        $this->assertSame($event, $this->pump->GetEventChain()->Bottom());
        $this->assertSame($event->inner_event, $this->pump->GetEventChain()->Top());
    }

    public function testTerminate()
    {
        $this->pump = $this->getMock('phalanx\events\EventPump', array('_Exit'));
        $msg = 'testing 1 2 3';

        ob_start();
        $this->pump->expects($this->once())->method('_Exit');
        $this->pump->Terminate($msg);
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($msg, $result);
    }

    public function testGetEventChain()
    {
        $event = new TestEvent();
        $this->pump->PostEvent($event);
        $this->assertEquals(1, $this->pump->GetEventChain()->Count());
        $this->assertSame($event, $this->pump->GetEventChain()->Top());
    }

    public function testGetLongerEventChain()
    {
        $event1 = new TestEvent();
        $event1->name = 'first';
        $event2 = new TestEvent();
        $event2->name = 'second';
        $this->pump->PostEvent($event1);
        $this->pump->PostEvent($event2);
        $this->assertEquals(2, $this->pump->GetEventChain()->Count());
        $this->assertSame($event2, $this->pump->GetEventChain()->Top());
        $this->assertSame($event1, $this->pump->GetEventChain()->Bottom());
    }
}
