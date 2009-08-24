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

class EventPumpTest extends \PHPUnit_Framework_TestCase
{
	public $pump;
	
	public function setUp()
	{
		$this->pump = new EventPump();
	}
	
	public function testSharedPump()
	{
		// Reset.
		EventPump::T_set_pump(null);
		
		$this->assertNotNull(EventPump::pump(), 'Did not create shared pump.');
		$this->assertNotSame($this->pump, EventPump::pump());
		
		EventPump::set_pump($this->pump);
		$this->assertSame($this->pump, EventPump::pump());
	}
	
	public function testRaiseWithoutContext()
	{
		$this->setExpectedException('\phalanx\events\EventPumpException');
		$this->pump->raise(new TestEvent());
	}
	
	public function testSetContext()
	{
		$context = new events\Context();
		
		$this->assertNull($this->pump->context(), 'Event pump has a context when it should not.');
		
		$this->pump->set_context($context);
		$this->assertSame($context, $this->pump->context());
	}
	
	public function testGetLastEvent()
	{
		$this->assertNull($this->pump->getLastEvent());
		
		$this->pump->set_context(new events\Context());
		
		$event1 = new TestEvent();
		$this->pump->raise($event1);
		$this->assertSame($event1, $this->pump->getLastEvent());
		$this->assertTrue($event1->did_init);
		$this->assertTrue($event1->did_handle);
		$this->assertTrue($event1->did_end);
		
		$event2 = new InitOnlyEvent();
		$this->pump->raise($event2);
		$this->assertSame($event2, $this->pump->getLastEvent());
		$this->assertTrue($event2->did_init);
		$this->assertFalse($event2->did_handle);
		$this->assertTrue($event2->did_end);
		
		$event3 = new TestEvent();
		$this->pump->raise($event3);
		$this->assertSame($event3, $this->pump->getLastEvent());
		$this->assertTrue($event3->did_init);
		$this->assertTrue($event3->did_handle);
		$this->assertTrue($event3->did_end);
	}
	
	public function testGetCurrentEvent()
	{
		$this->pump->set_context(new events\Context());
		
		$event1 = new TestEvent();
		$this->pump->raise($event1);
		$this->assertSame($event1, $this->pump->getCurrentEvent());
		
		$event2 = new InitOnlyEvent();
		$this->pump->raise($event2);
		$this->assertSame($event1, $this->pump->getCurrentEvent());
		
		$event3 = new TestEvent();
		$this->pump->raise($event3);
		$this->assertSame($event3, $this->pump->getCurrentEvent());
	}
	
	public function testRaiseEventOutputBuffering()
	{
		$event = new PrintEvent();
		$this->pump->set_context(new events\Context());
		
		ob_start();
		$this->pump->raise($event);
		$buffer = ob_get_contents();
		ob_end_clean();
		
		$this->assertEquals('init().handle().end().', $buffer);
	}
}
