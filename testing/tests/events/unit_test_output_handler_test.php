<?php
// Phalanx
// Copyright (c) 2010 Blue Static
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

class UnitTestOutputHandlerTest extends \PHPUnit_Framework_TestCase
{
    public $handler;
    public $pump;

    public function setUp()
    {
        $this->pump    = $this->getMock('phalanx\events\EventPump', array('_Exit'));
        events\EventPump::T_set_pump($this->pump);
        $this->handler = new events\UnitTestOutputHandler();
        $this->pump->set_output_handler($this->handler);
    }

    public function testDoStart()
    {
        $event = new TestEvent();
        $event->id = 'foo';
        $this->pump->PostEvent($event);

        $event = new TestEvent();
        $event->id = 'bar';
        $this->pump->PostEvent($event);

        $event = new TestEvent();
        $event->id = 'baz';
        $this->pump->PostEvent($event);

        $this->pump->StopPump();

        // Event chain is newest to oldest.
        $data = $this->handler->event_data();
        $this->assertEquals('baz', $data[0]->id);
        $this->assertEquals('bar', $data[1]->id);
        $this->assertEquals('foo', $data[2]->id);
    }
}
