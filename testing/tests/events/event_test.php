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

require_once 'PHPUnit/Framework.php';

class EventTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->event = new TestEvent();
	}
	
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
		$context = new events\Context();
		
		$event = new TestEvent();
		$this->assertNull($event->context(), 'Event created with a Context');
		
		$event->set_context($context);
		$this->assertSame($context, $event->context());
		
		$event = new TestEvent($context);
		$this->assertSame($context, $event->context());
	}
	
	public function testCanRunInContext()
	{
		$context1 = new events\Context();
		$context2 = new BadContext();
		
		$this->assertTrue(TestEvent::canRunInContext($context1));
		$this->assertFalse(TestEvent::canRunInContext($context2));
		
		$this->assertTrue(events\Event::canRunInContext($context1));
		$this->assertTrue(events\Event::canRunInContext($context2));
	}
	
	public function testCancel()
	{
		$event = new TestEvent();
		$this->assertFalse($event->is_cancelled());
		$event->cancel();
		$this->assertTrue($event->is_cancelled());
	}
	
	public function testSetAndAppendOutput()
	{
		$this->assertEquals($this->event->output(), '');
		
		$this->event->append_output('Append.');
		$this->assertEquals($this->event->output(), 'Append.');
		
		$this->event->set_output('Reset.');
		$this->assertEquals($this->event->output(), 'Reset.');
		
		$this->event->append_output('Append.');
		$this->assertEquals($this->event->output(), 'Reset.Append.');
	}
}
