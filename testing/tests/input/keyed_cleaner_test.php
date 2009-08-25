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
	public $fixture;
	public $cleaner;
	
	public function setUp()
	{
		$this->fixture = array(
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
		$this->cleaner = new input\KeyedCleaner($this->fixture);
	}
	
	public function testConstructWithRef()
	{
		$test = $this->cleaner->ref();
		$this->assertEquals('string', $test['str']);
	}
	
	public function testGetKeyer()
	{
		$this->assertEquals('phalanx\base\KeyDescender', get_class($this->cleaner->keyer()));
	}
	
	public function testCleanString()
	{
		$this->cleaner->getString('str');
		$this->assertEquals('string', $this->fixture['str']);

		$this->cleaner->getString('tstr');
		$this->assertEquals(' trimmed string	', $this->fixture['tstr']);

		$this->cleaner->getString('html');
		$this->assertEquals('<strong>html</strong>', $this->fixture['html']);

		$this->cleaner->getString('dqstr');
		$this->assertEquals('"double quoted string"', $this->fixture['dqstr']);

		$this->cleaner->getString('sqstr');
		$this->assertEquals("'single quoted string'", $this->fixture['sqstr']);

		$this->cleaner->getString('dqhtml');
		$this->assertEquals('<strong>html with "double quotes"</strong>', $this->fixture['dqhtml']);

		$this->cleaner->getString('entity');
		$this->assertEquals('red, green, & blue', $this->fixture['entity']);
	}
	
	public function testCleanTrimmedStr()
	{
		$this->cleaner->getTrimmedString('str');
		$this->assertEquals('string', $this->fixture['str']);

		$this->cleaner->getTrimmedString('tstr');
		$this->assertEquals('trimmed string', $this->fixture['tstr']);
	}
	
	public function testCleanHTML()
	{
		$this->cleaner->getHTML('str');
		$this->assertEquals('string', $this->fixture['str']);

		$this->cleaner->getHTML('html');
		$this->assertEquals('&lt;strong&gt;html&lt;/strong&gt;', $this->fixture['html']);

		$this->cleaner->getHTML('dqstr');
		$this->assertEquals('&quo;double quoted string&quo;', $this->fixture['dqstr']);

		$this->cleaner->getHTML('sqstr');
		$this->assertEquals("'single quoted string'", $this->fixture['sqstr']);

		$this->cleaner->getHTML('dqhtml');
		$this->assertEquals('&lt;strong&gt;html with &quo;double quotes&quo;&lt;/strong&gt;', $this->fixture['dqhtml']);

		$this->cleaner->getHTML('entity');
		$this->assertEquals('red, green, & blue', $this->fixture['entity']);
	}
	
	public function testCleanInt()
	{
		$this->cleaner->getInt('str');
		$this->assertEquals(0, $this->fixture['str']);

		$this->cleaner->getInt('int');
		$this->assertEquals(42, $this->fixture['int']);

		$this->cleaner->getInt('float');
		$this->assertEquals(3, $this->fixture['float']);

		$this->cleaner->getInt('bool1');
		$this->assertEquals(1, $this->fixture['bool1']);
	}
	
	public function testCleanFloat()
	{
		$this->cleaner->getFloat('str');
		$this->assertEquals(0.0, $this->fixture['str']);

		$this->cleaner->getFloat('int');
		$this->assertEquals(42.0, $this->fixture['int']);

		$this->cleaner->getFloat('float');
		$this->assertEquals(3.14159, $this->fixture['float']);
	}
	
	public function testCleanBool()
	{
		$this->cleaner->getBool('bool1');
		$this->assertEquals(true, $this->fixture['bool1']);

		$this->cleaner->getBool('boolT');
		$this->assertEquals(true, $this->fixture['boolT']);

		$this->cleaner->getBool('boolY');
		$this->assertEquals(true, $this->fixture['boolY']);

		$this->cleaner->getBool('bool0');
		$this->assertEquals(false, $this->fixture['bool0']);

		$this->cleaner->getBool('boolF');
		$this->assertEquals(false, $this->fixture['boolF']);

		$this->cleaner->getBool('boolN');
		$this->assertEquals(false, $this->fixture['boolN']);
	}
}
