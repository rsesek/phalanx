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
use \phalanx\events as events;

require_once 'PHPUnit/Framework.php';

class MessageEvent extends events\Event
{
    public $message = null;

    static public function InputList()
    {
        return array();
    }

    static public function OutputList()
    {
        return array('message');
    }

    public function __construct($msg)
    {
        $this->message = $msg;
    }

    public function Fire() {}
}

class CLIOutputHandlerTest extends \PHPUnit_Framework_TestCase
{
    public $handler;
    public $pump;

    public function setUp()
    {
        $this->handler = new events\CLIOutputHandler();
        $this->pump    = $this->getMock('phalanx\events\EventPump', array('_Exit'));
        $this->pump->set_output_handler($this->handler);
        events\EventPump::T_set_pump($this->pump);
    }

    protected function _GetOutput()
    {
        ob_start();
        $this->pump->StopPump();
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    public function testSingleMessage()
    {
        $this->pump->PostEvent(new MessageEvent('First Message!'));

        $expected = "First Message!\n";
        $this->assertEquals($expected, $this->_GetOutput());
    }

    public function testMultipleMessages()
    {
        $this->pump->PostEvent(new MessageEvent('Message One'));
        $this->pump->PostEvent(new MessageEvent('Message Two'));

        $expected = "Message Two\nMessage One\n";
        $this->assertEquals($expected, $this->_GetOutput());
    }
}
