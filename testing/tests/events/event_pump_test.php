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
		EventPump::T_set_pump(NULL);
		
		$this->assertNotNull(EventPump::Pump(), 'Did not create shared pump.');
		$this->assertNotSame($this->pump, EventPump::Pump());
		
		EventPump::set_pump($this->pump);
		$this->assertSame($this->pump, EventPump::Pump());
	}
	
	public function testGetCurrentEvent()
	{
		$event1 = new TestEvent();
		$this->pump->PostEvent($event1);
		$this->assertSame($event1, $this->pump->GetCurrentEvent());
		
		$event2 = new InitOnlyEvent();
		$this->pump->PostEvent($event2);
		$this->assertSame($event1, $this->pump->GetCurrentEvent());
		
		$event3 = new TestEvent();
		$this->pump->PostEvent($event3);
		$this->assertSame($event3, $this->pump->GetCurrentEvent());
	}

    public function testSetOutputHandler()
    {
        $this->assertNull($this->pump->output_handler());

        $handler = new TestOutputHandler();
        $this->pump->set_output_handler($handler);
        $this->assertSame($handler, $this->pump->output_handler());
    }
}
