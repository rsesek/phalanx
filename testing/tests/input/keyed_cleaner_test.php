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

class KeyedCleanerTest extends \PHPUnit_Framework_TestCase
{
	public $cleaner;
	
	public function setUp()
	{
		$array = array(
			'str'		=> 'string',
			'tstr'	=> ' trimmed string	',
			'html'	=> '<strong>html</strong>',
			'dqstr'	=> '"double quoted string"',
			'sqstr'	=> "'single quoted string'",
			'dqhtml'	=> '<strong>html with "double quotes"</strong>',
			'int'		=> 42,
			'float'	=> 3.14159,
			'bool1'	=> true,
			'bool0'	=> false,
			'boolT'	=> 'TrRuE',
			'boolF'	=> 'false',
			'boolY'	=> 'YES',
			'boolN'	=> 'no',
			'entity' => 'red, green, & blue'
		);
		$this->cleaner = new input\KeyedCleaner($array);
	}
	
	public function testConstructWithRef()
	{
		$test = $this->cleaner->ref();
		$this->assertEquals('string', $test['str']);
	}
	
	public function testCleanString()
	{
		$this->assertEquals('string', $this->cleaner->getString('str'));
		$this->assertEquals(' trimmed string	', $this->cleaner->getString('tstr'));
		$this->assertEquals('<strong>html</strong>', $this->cleaner->getString('html'));
		$this->assertEquals('"double quoted string"', $this->cleaner->getString('dqstr'));
		$this->assertEquals("'single quoted string'", $this->cleaner->getString('sqstr'));
		$this->assertEquals('<strong>html with "double quotes"</strong>', $this->cleaner->getString('dqhtml'));
		$this->assertEquals('red, green, & blue', $this->cleaner->getString('entity'));
	}
	
	public function testCleanTrimmedStr()
	{
		$this->assertEquals('string', $this->cleaner->getTrimmedString('str'));
		$this->assertEquals('trimmed string', $this->cleaner->getTrimmedString('tstr'));
	}
	
	public function testCleanHTML()
	{
		$this->assertEquals('string', $this->cleaner->getHTML('str'));
		$this->assertEquals('&lt;strong&gt;html&lt;/strong&gt;', $this->cleaner->getHTML('html'));
		$this->assertEquals('&quo;double quoted string&quo;', $this->cleaner->getHTML('dqstr'));
		$this->assertEquals("'single quoted string'", $this->cleaner->getHTML('sqstr'));
		$this->assertEquals('&lt;strong&gt;html with &quo;double quotes&quo;&lt;/strong&gt;', $this->cleaner->getHTML('dqhtml'));
		$this->assertEquals('red, green, & blue', $this->cleaner->getHTML('entity'));
	}
	
	public function testCleanInt()
	{
		$this->assertEquals(0, $this->cleaner->getInt('str'));
		$this->assertEquals(42, $this->cleaner->getInt('int'));
		$this->assertEquals(3, $this->cleaner->getInt('float'));
		$this->assertEquals(1, $this->cleaner->getInt('bool1'));
	}
	
	public function testCleanFloat()
	{
		$this->assertEquals(0.0, $this->cleaner->getFloat('str'));
		$this->assertEquals(42.0, $this->cleaner->getFloat('int'));
		$this->assertEquals(3.14159, $this->cleaner->getFloat('float'));
	}
	
	public function testCleanBool()
	{
		$this->assertEquals(true, $this->cleaner->getBool('bool1'));
		$this->assertEquals(true, $this->cleaner->getBool('boolT'));
		$this->assertEquals(true, $this->cleaner->getBool('boolY'));
		$this->assertEquals(false, $this->cleaner->getBool('bool0'));
		$this->assertEquals(false, $this->cleaner->getBool('boolF'));
		$this->assertEquals(false, $this->cleaner->getBool('boolN'));
	}
}
