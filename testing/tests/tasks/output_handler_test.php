<?php
// Phalanx
// Copyright (c) 2009-2010 Blue Static
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

class OutputHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $handler;

    public function setUp()
    {
        $this->handler = new TestOutputHandler();
    }

    public function testStart()
    {
        $this->assertFalse($this->handler->do_start);
        $this->handler->Start();
        $this->assertTrue($this->handler->do_start);
    }

    public function testGetTaskData()
    {
        $input = new \phalanx\base\PropertyBag(array('foo' => 'bar'));
        $task = new TestTask($input);
        $task->Fire();
        $data  = $this->handler->T_GetTaskData($task);
        $expected = array(
            'will_fire'  => FALSE,
            'fire'       => TRUE,
            'cleanup'    => FALSE,
            'out1'       => 'foo',
            'out2'       => 'bar',
            'out3'       => 'moo',
            'id'         => NULL,
            'input'      => $input
        );
        $this->assertType('phalanx\base\PropertyBag', $data);
        $this->assertEquals($expected, $data->ToArray());
        $this->assertFalse($task->out2_never_true);
    }
}
