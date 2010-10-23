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
use \phalanx\tasks as events;

require_once 'PHPUnit/Framework.php';

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher;

    public function setUp()
    {
        $this->dispatcher = new TestDispatcher();
    }

    public function testSetLoader()
    {
        $this->assertNull($this->dispatcher->task_loader());

        $fun = function($task_name) {
            return '\phalanx\test\TestTask';
        };
        $this->dispatcher->set_task_loader($fun);
        $this->assertSame($fun, $this->dispatcher->task_loader());
    }

    public function testSetPump()
    {
        $this->assertNotNull($this->dispatcher->pump());
        $this->assertSame(events\TaskPump::Pump(), $this->dispatcher->pump());

        $pump = new events\TaskPump();
        $this->dispatcher->set_pump($pump);
        $this->assertSame($pump, $this->dispatcher->pump());
    }

    public function testStart()
    {
        $pump = $this->getMock('phalanx\tasks\TaskPump');
        $this->dispatcher->set_pump($pump);
        $pump->expects($this->once())->method('QueueTask')->with(
            $this->isInstanceOf('phalanx\test\TestTask')
        );

        $loader = function($task_name) {
            return '\phalanx\test\TestTask';
        };
        $this->dispatcher->set_task_loader($loader);

        $this->dispatcher->Start();
    }

    public function testBypassRules()
    {
        $this->assertNull($this->dispatcher->GetBypassRule('foo'));
        $this->dispatcher->AddBypassRule('foo', 'moo');
        $this->assertEquals('moo', $this->dispatcher->GetBypassRule('foo'));
        $this->dispatcher->RemoveBypassRule('foo');
        $this->assertNull($this->dispatcher->GetBypassRule('foo'));
    }
}
