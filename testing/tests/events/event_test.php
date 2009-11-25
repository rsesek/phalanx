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
    protected $event;

    public function setUp()
    {
        $this->event = new TestEvent();
    }

    public function testInput()
    {
        $args = new \phalanx\base\PropertyBag();
        $args->test = 'foo';
        $this->event = new TestEvent($args);
        $this->assertSame($args, $this->event->input());
    }

    public function testCancel()
    {
        $event = new TestEvent();

        $pump = $this->getMock('phalanx\events\EventPump');
        $pump->expects($this->once())->method('Cancel')->with($event);
        \phalanx\events\EventPump::T_set_pump($pump);

        $this->assertFalse($event->is_cancelled());
        $event->Cancel();
    }
}
