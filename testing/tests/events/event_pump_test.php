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
	public function setUp()
	{
		EventPump::set_pump(new EventPump());
	}
	
	public function testSharedPump()
	{
		// Reset.
		EventPump::T_set_pump(null);
		
		$test = new EventPump();
		
		$this->assertNotNull(EventPump::pump(), 'Did not create shared pump.');
		$this->assertFalse(EventPump::pump() === $test);
		
		EventPump::set_pump($test);
		$this->assertSame(EventPump::pump(), $test);
	}
	
	public function testRaiseWithoutContext()
	{
		$this->setExpectedException('\phalanx\events\EventPumpException');
		EventPump::pump()->raise(new TestEvent());
	}
	
	public function testSetContext()
	{
		$context = new events\Context();
		
		$this->assertNull(EventPump::pump()->context(), 'Event pump has a context when it should not.');
		
		EventPump::pump()->set_context($context);
		$this->assertSame($context, EventPump::pump()->context());
	}
	
	public function testGetLastEvent()
	{
		$this->assertNull(EventPump::pump()->getLastEvent());
		
		EventPump::pump()->set_context(new events\Context());
		
		$event1 = new TestEvent();
		EventPump::pump()->raise($event1);
		$this->assertSame($event1, EventPump::pump()->getLastEvent());
		
		$event2 = new TestEvent();
		EventPump::pump()->raise($event2);
		$this->assertSame($event2, EventPump::pump()->getLastEvent());
	}
	
	public function testGetCurrentEvent()
	{
		EventPump::pump()->set_context(new events\Context());
		
		$event1 = new TestEvent();
		EventPump::pump()->raise($event1);
		$this->assertSame($event1, EventPump::pump()->getCurrentEvent());
		
		
	}
}
