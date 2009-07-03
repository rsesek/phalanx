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
	
	public function testGetLastEvent()
	{
		$this->assertEquals(EventPump::pump()->getLastEvent(), null);
		
		$event = new TestEvent();
		
		EventPump::pump()->raise($event);
		
		$this->assertSame($event, EventPump::pump()->getLastEvent());
	}
}
