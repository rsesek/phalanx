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
use \phalanx\input as input;

require_once 'PHPUnit/Framework.php';

// Common includes.
require PHALANX_ROOT . '/input/cleaner.php';
require PHALANX_ROOT . '/input/form_key.php';
require PHALANX_ROOT . '/input/keyed_cleaner.php';

class InputSuite
{
	static public function suite()
	{
		$suite = new \PHPUnit_Framework_TestSuite('Input');
		
		$suite->addTestFile(TEST_ROOT . '/tests/input/cleaner_test.php');
		$suite->addTestFile(TEST_ROOT . '/tests/input/form_key_test.php');
		$suite->addTestFile(TEST_ROOT . '/tests/input/keyed_cleaner_test.php');
		
		return $suite;
	}
}

class TestFormKeyManagerDelegate implements input\FormKeyManagerDelegate
{
	public $did_get = FALSE;
	public $did_save = FALSE;
	public $did_delete = FALSE;
	
	public $key_storage = array();
	
	public function getFormKey($key)
	{
		$this->did_get = TRUE;
		return $this->key_storage[$key];
	}
	
	public function saveFormKey(\phalanx\base\PropertyBag $form_key)
	{
		$this->did_save = TRUE;
		$this->key_storage[$form_key->key] = $form_key;
	}
	
	public function deleteKey($key)
	{
		$this->did_delete = TRUE;
		unset($this->key_storage[$key]);
	}
}
