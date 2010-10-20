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
require_once TEST_ROOT . '/tests/events.php';

class TestCLIDispatcher extends events\CLIDispatcher
{
    public function T_set_cli_input($input)
    {
        $this->cli_input = $input;
    }
    public function T_ParseArguments($args)
    {
        return $this->_ParseArguments($args);
    }
    public function T_GetEventName()
    {
        return $this->_GetEventName();
    }
    public function T_GetInput(Array $keys)
    {
        return $this->_GetInput($keys);
    }
}

function _Args()
{
    $arguments = func_get_args();
    array_unshift($arguments, 'test.php');
    return $arguments;
}

class CLIDispatcherTest extends \PHPUnit_Framework_TestCase
{
    // PHPUnit Configuration {{
        protected $backupGlobals = TRUE;
    // }}
    protected $dispatcher;

    public function setUp()
    {
        $this->dispatcher = new TestCLIDispatcher();
    }

    public function testParseNoArguments()
    {
        $params = $this->dispatcher->T_ParseArguments(_Args('test-event'));
        $this->assertEquals('test-event', $params->_event);
    }

    public function testParseIDArgument()
    {
        $params = $this->dispatcher->T_ParseArguments(_Args('test-event', '42'));
        $this->assertEquals('test-event', $params->_event);
        $this->assertEquals('42', $params->_id);
    }

    public function testParseWith1Pair()
    {
        $params = $this->dispatcher->T_ParseArguments(_Args('test-event', '--flag', 'value'));
        $this->assertEquals('test-event', $params->_event);
        $this->assertEquals('value', $params->flag);
    }

    public function testParseWith2Pair()
    {
        $params = $this->dispatcher->T_ParseArguments(_Args('test', '--k1', 'v1', '--k2', 'v2'));
        $this->assertEquals('test', $params->_event);
        $this->assertEquals('v1', $params->k1);
        $this->assertEquals('v2', $params->k2);
    }

    public function testParseWithBadPair()
    {
        $this->setExpectedException('phalanx\events\CLIDispatcherException');
        $params = $this->dispatcher->T_ParseArguments(_Args('test', '--k1', 'v1', '--k2'));
    }

    public function testParseWithBadFlag()
    {
      $this->setExpectedException('phalanx\events\CLIDispatcherException');
      $params = $this->dispatcher->T_ParseArguments(_Args('test', '-k1', 'v1'));
    }

    public function testGetEventName()
    {
        $input = $this->dispatcher->T_ParseArguments(_Args('test-event', '--key', 'value'));
        $this->dispatcher->T_set_cli_input($input);
        $this->assertEquals('test-event', $this->dispatcher->T_GetEventName());
    }

    public function testGetInput()
    {
        $args = _Args('test-event', '--key1', 'value', '--flag', 'special');
        $this->dispatcher->T_set_cli_input($this->dispatcher->T_ParseArguments($args));
        $gathered_input = $this->dispatcher->T_GetInput(TestEvent::InputList());
        $this->assertEquals(1, $gathered_input->Count());
        $this->assertEquals('value', $gathered_input->key1);
        $this->assertNull($gathered_input->key2);
    }
}
