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
use \phalanx\tasks as tasks;

class UnitTestOutputHandlerTest extends \PHPUnit_Framework_TestCase
{
    public $handler;
    public $pump;

    public function setUp()
    {
        $this->pump    = $this->getMock('phalanx\tasks\TaskPump', array('_Exit'));
        tasks\TaskPump::T_set_pump($this->pump);
        $this->handler = new tasks\UnitTestOutputHandler();
        $this->pump->set_output_handler($this->handler);
    }

    public function testDoStart()
    {
        $task = new TestTask();
        $task->id = 'foo';
        $this->pump->QueueTask($task);

        $task = new TestTask();
        $task->id = 'bar';
        $this->pump->QueueTask($task);

        $task = new TestTask();
        $task->id = 'baz';
        $this->pump->QueueTask($task);

        $this->pump->StopPump();

        // Task chain is newest to oldest.
        $data = $this->handler->task_data();
        $this->assertEquals('baz', $data[0]->id);
        $this->assertEquals('bar', $data[1]->id);
        $this->assertEquals('foo', $data[2]->id);
    }
}
