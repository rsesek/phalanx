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
	// This test must be first.
	public function testSharedPump()
	{
		$test = new EventPump();
		
		$this->assertNotNull(EventPump::pump(), 'Did not create shared pump.');
		$this->assertFalse(EventPump::pump() === $test);
		
		EventPump::set_pump($test);
		$this->assertSame(EventPump::pump(), $test);
	}
	
	public function testRaiseWithoutContext()
	{
		try
		{
			EventPump::pump()->raise(new TestEvent());
			$this->fail('\phalanx\events\EventPumpException expected');
		}
		catch (\Exception $e)
		{
			$this->assertThat($e, $this->isInstanceOf('\phalanx\events\EventPumpException'));
		}
	}
	
	public function testSetContext()
	{
		$context = new events\Context();
		
		$this->assertEquals(EventPump::pump()->context(), null, 'Event pump has a context.');
		
		EventPump::pump()->set_context($context);
		$this->assertSame($context, EventPump::pump()->context());
	}
	
	public function testGetLastEvent()
	{
		$this->assertEquals(EventPump::pump()->getLastEvent(), null);
		
		$event1 = new TestEvent();
		EventPump::pump()->raise($event1);
		$this->assertSame($event1, EventPump::pump()->getLastEvent());
		
		$event2 = new TestEvent();
		EventPump::pump()->raise($event2);
		$this->assertSame($event2, EventPump::pump()->getLastEvent());
	}
}
