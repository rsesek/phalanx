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

class NestedEvent extends TestEvent
{
    public $test;
    public $inner_event;

    public function Fire()
    {
        $count = $this->test->pump->GetDeferredEvents()->Count();
        $this->test->pump->PostEvent($this->inner_event);
        $this->test->assertEquals($count+1, $this->test->pump->GetDeferredEvents()->Count());
        parent::Fire();
    }

    public function Cleanup()
    {
        $this->test = NULL;
        parent::Cleanup();
    }
}

class PreemptedEvent extends TestEvent
{
    public $test;
    public $inner_event;

    public function Fire()
    {
        $this->test->pump->RaiseEvent($this->inner_event);
        parent::Fire();
    }

    public function Cleanup()
    {
        // Makes print_f() on these objects manageable.
        $this->test = NULL;
        parent::Cleanup();
    }
}

class CancelledEvent extends TestEvent
{
    public $test;

    public function Fire()
    {
        $this->test->pump->Cancel($this);
    }

    public function Cleanup()
    {
        $this->test = NULL;
        parent::Cleanup();
    }
}

class CancelledWillFireEvent extends TestEvent
{
    public $test;

    public function WillFire()
    {
        parent::WillFire();
        $this->test->pump->Cancel($this);
    }

    public function Cleanup()
    {
        $this->test = NULL;
        parent::Cleanup();
    }
}

class PreemptedCancelledEvent extends CancelledEvent
{
    public $test;
    public $inner_event;

    public function Fire()
    {
        $this->test->pump->RaiseEvent($this->inner_event);
        parent::Fire();
    }
}

class CurrentEventTester extends TestEvent
{
    public $test;
    public $inner_event;

    public function WillFire()
    {
        $this->test->assertEquals($this, $this->test->pump->GetCurrentEvent());
        $this->test->assertEquals(EventPump::EVENT_WILL_FIRE, $this->test->pump->current_event_state());
    }
    public function Fire()
    {
        $this->test->assertEquals($this, $this->test->pump->GetCurrentEvent());
        $this->test->assertEquals(EventPump::EVENT_FIRE, $this->test->pump->current_event_state());
        if ($this->inner_event)
            $this->test->pump->RaiseEvent($this->inner_event);
    }
    public function Cleanup()
    {
        $this->test->assertEquals(EventPump::EVENT_CLEANUP, $this->test->pump->current_event_state());
        $this->test->assertEquals($this, $this->test->pump->GetCurrentEvent());
    }
}

class StopPumpEvent extends TestEvent
{
    public $test;

    public function Fire()
    {
        parent::Fire();
        $this->test->pump->StopPump();
    }
}

class GetEventChainEvent extends TestEvent
{
    public $test;

    public function Fire()
    {
        parent::Fire();
        $chain = $this->test->pump->GetEventChain();
        $this->test->assertSame($this, $chain->Top());
    }

    public function Cleanup()
    {
        $this->test = NULL;
    }
}

class EventPumpTest extends \PHPUnit_Framework_TestCase
{
    public $pump;

    public function setUp()
    {
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
        $event->test = $this;
        $this->pump->PostEvent($event);

        $event = new CurrentEventTester();
        $event->test = $this;
        $event->inner_event = new CurrentEventTester();
        $event->inner_event->test = $this;
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
        $event->test        = $this;
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

        // |$this->inner_event| finished executing before |$event|, so it
        // should be further down the stack.
        $this->assertSame($inner_event, $this->pump->GetEventChain()->Bottom());
        $this->assertSame($event, $this->pump->GetEventChain()->Top());
    }

    public function testCancel()
    {
        $event = new CancelledEvent();
        $event->test = $this;

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
        $event->test = $this;

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
        $event->test        = $this;
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
        $event->test        = $this;
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
        $event->test = $this;
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
        $event = new GetEventChainEvent();
        $event->test = $this;
        $this->pump->PostEvent($event);
    }

    public function testGetLongerEventChain()
    {
        $event1 = new TestEvent();
        $event2 = new GetEventChainEvent();
        $event2->test = $this;
        $this->pump->PostEvent($event1);
        $this->pump->PostEvent($event2);
        $this->assertEquals(2, $this->pump->GetEventChain()->Count());
        $this->assertSame($event2, $this->pump->GetEventChain()->Top());
        $this->assertSame($event1, $this->pump->GetEventChain()->Bottom());
    }
}
