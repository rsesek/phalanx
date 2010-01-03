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

// Common includes.
require PHALANX_ROOT . '/events/dispatcher.php';
require PHALANX_ROOT . '/events/event.php';
require PHALANX_ROOT . '/events/event_pump.php';
require PHALANX_ROOT . '/events/http_dispatcher.php';
require PHALANX_ROOT . '/events/output_handler.php';
require PHALANX_ROOT . '/events/view_output_handler.php';
require PHALANX_ROOT . '/events/unit_test_output_handler.php';

class EventsSuite
{
    static public function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite('Events');

        $suite->addTestFile(TEST_ROOT . '/tests/events/dispatcher_test.php');
        $suite->addTestFile(TEST_ROOT . '/tests/events/event_test.php');
        $suite->addTestFile(TEST_ROOT . '/tests/events/event_pump_test.php');
        $suite->addTestFile(TEST_ROOT . '/tests/events/http_dispatcher_test.php');
        $suite->addTestFile(TEST_ROOT . '/tests/events/output_handler_test.php');
        $suite->addTestFile(TEST_ROOT . '/tests/events/view_output_handler_test.php');
        $suite->addTestFile(TEST_ROOT . '/tests/events/unit_test_output_handler_test.php');

        return $suite;
    }
}

// Common fixtures.

class TestEvent extends events\Event
{
    public $will_fire = FALSE;
    public $fire = FALSE;
    public $cleanup = FALSE;

    public $out1;
    public $out2;
    public $out2_never_true = FALSE;

    public $id = NULL;

    // The property should hide this from OutputHandler::_GetEventData().
    public function out2()
    {
        $this->out2_never_true = TRUE;
    }

    public function out3()
    {
        return 'moo';
    }

    static public function InputList()
    {
        return array('key1', 'key2');
    }

    static public function OutputList()
    {
        return array('will_fire', 'fire', 'cleanup', 'out1', 'out2', 'out3', 'no_out', 'id');
    }

    public function WillFire()
    {
        $this->will_fire = TRUE;
        parent::WillFire();  // Boost code coverage. No-op.
    }

    public function Fire()
    {
        $this->fire = TRUE;
        $this->out1 = 'foo';
        $this->out2 = 'bar';
    }

    public function Cleanup()
    {
        $this->cleanup = TRUE;
    }
}

class InitOnlyEvent extends TestEvent
{
    public function WillFire()
    {
        parent::WillFire();
        $this->Cancel();
    }
}

class TestOutputHandler extends events\OutputHandler
{
    public $do_start = FALSE;

    protected function _DoStart()
    {
        $this->do_start = TRUE;
    }

    public function T_GetEventData(events\Event $event)
    {
        return $this->_GetEventData($event);
    }
}

class TestDispatcher extends events\Dispatcher
{
    protected function _GetEventName()
    {
        return 'event.test';
    }

    protected function _GetInput(Array $input_list)
    {
        $input = new \phalanx\base\PropertyBag();
        foreach ($input_list as $key)
            $input->Set($key, 'test:' . $key);
        return $input;
    }
}
