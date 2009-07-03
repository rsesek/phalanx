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
use \phalanx\events\Event;

require_once 'PHPUnit/Framework.php';

class EventTest extends \PHPUnit_Framework_TestCase
{
	public function testTime()
	{
		$before = new \DateTime();
		sleep(1);
		$event = new TestEvent();
		sleep(1);
		$after = new \DateTime();
		
		$time = $event->time();
		$this->assertNotNull($time);
		
		$this->assertLessThan($time->getTimestamp(), $before->getTimestamp());
		$this->assertGreaterThan($time->getTimestamp(), $after->getTimestamp());
	}
	
	public function testSetContext()
	{
		$context = new Context();
		$context->identifier = 'foo';
		
		$event = new TestEvent();
		$this->assertEquals($event->context(), null, 'Event created with a Context');
		
		$event->set_context($context);
		$this->assertSame($context, $event->context());
		
		$event = new Event($context);
		$this->assertSame($context, $event->context());
	}
}

class TestEvent extends Event
{
	public function handle()
	{
		// Do nothing.
	}
}
