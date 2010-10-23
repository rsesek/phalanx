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
use \phalanx\tasks as tasks;

require_once 'PHPUnit/Framework.php';

class TaskTest extends \PHPUnit_Framework_TestCase
{
    protected $task;

    public function setUp()
    {
        $this->task = new TestTask();
    }

    public function testInput()
    {
        $args = new \phalanx\base\PropertyBag();
        $args->test = 'foo';
        $this->task = new TestTask($args);
        $this->assertSame($args, $this->task->input());
    }

    public function testCancel()
    {
        $task = new TestTask();

        $pump = $this->getMock('phalanx\tasks\TaskPump');
        $pump->expects($this->once())->method('Cancel')->with($task);
        \phalanx\tasks\TaskPump::T_set_pump($pump);

        $this->assertFalse($task->is_cancelled());
        $task->Cancel();
    }
}
