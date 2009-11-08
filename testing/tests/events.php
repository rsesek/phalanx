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
require PHALANX_ROOT . '/events/event.php';
require PHALANX_ROOT . '/events/event_pump.php';

class EventsSuite
{
	public static function suite()
	{
		$suite = new \PHPUnit_Framework_TestSuite('Events');
		
		$suite->addTestFile(TEST_ROOT . '/tests/events/event_test.php');
		$suite->addTestFile(TEST_ROOT . '/tests/events/event_pump_test.php');
		
		return $suite;
	}
}

// Common fixtures.

class TestEvent extends events\Event
{
	public $will_fire = FALSE;
	public $fire = FALSE;
	public $cleanup = FALSE;

    static public function InputList()
    {
        return array('key1', 'key2');
    }

    static public function OutputList()
    {
        return array('will_fire', 'fire', 'cleanup');
    }

	public function WillFire()
	{
		$this->will_fire = TRUE;
	}
	
	public function Fire()
	{
		$this->fire = TRUE;
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
