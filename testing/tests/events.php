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

// Common includes.
require PHALANX_ROOT . '/events/context.php';
require PHALANX_ROOT . '/events/event.php';
require PHALANX_ROOT . '/events/event_pump.php';

class EventsSuite
{
	public static function suite()
	{
		$suite = new \PHPUnit_Framework_TestSuite('Events');
		
		$suite->addTestFile(TEST_ROOT . '/tests/events/context_test.php');
		$suite->addTestFile(TEST_ROOT . '/tests/events/event_test.php');
		$suite->addTestFile(TEST_ROOT . '/tests/events/event_pump_test.php');
		
		return $suite;
	}
}

// Common fixtures.

class TestEvent extends events\Event
{
	public $did_init = false;
	public $did_handle = false;
	public $did_end = false;
	
	public static function canRunInContext(events\Context $c)
	{
		return !($c instanceof BadContext);
	}
	
	public function init()
	{
		$this->did_init = true;
	}
	
	public function handle()
	{
		$this->did_handle = true;
	}
	
	public function end()
	{
		$this->did_end = true;
	}
}

class BadContext extends events\Context
{
}

class InitOnlyEvent extends TestEvent
{
	public function init()
	{
		parent::init();
		$this->cancel();
	}
}

class PrintEvent extends events\Event
{
	public function init()
	{
		echo 'init().';
	}
	
	public function handle()
	{
		echo 'handle().';
	}
	
	public function end()
	{
		echo 'end().';
	}
}

class TestContext extends events\Context
{
	public $did_event_handled = false;
	
	public function onEventHandled(events\Event $event)
	{
		parent::onEventHandled($event);
		$this->did_event_handled = true;
	}
	
	// Getter and setters.
	// -------------------------------------------------------------------------
	public function T_gpc() { return $this->gpc; }
	public function T_set_gpc_var($gpc, $key, $value)
	{
		$this->gpc[$gpc][$key] = $value;
	}
}
