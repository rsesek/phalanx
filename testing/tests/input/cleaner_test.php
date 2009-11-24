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
use \phalanx\input\Cleaner as Cleaner;

require_once 'PHPUnit/Framework.php';

class CleanerTest extends \PHPUnit_Framework_TestCase
{
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
			'bool1'	=> TRUE,
			'bool0'	=> FALSE,
			'boolT'	=> 'TrRuE',
			'boolF'	=> 'FALSE',
			'boolY'	=> 'YES',
			'boolN'	=> 'no',
			'entity' => 'red, green, & blue'
		);
	}
	
	public function testCleanString()
	{
		$this->assertEquals('string', Cleaner::String($this->fixture['str']));
		$this->assertEquals(' trimmed string	', Cleaner::String($this->fixture['tstr']));
		$this->assertEquals('<strong>html</strong>', Cleaner::String($this->fixture['html']));
		$this->assertEquals('"double quoted string"', Cleaner::String($this->fixture['dqstr']));
		$this->assertEquals("'single quoted string'", Cleaner::String($this->fixture['sqstr']));
		$this->assertEquals('<strong>html with "double quotes"</strong>', Cleaner::String($this->fixture['dqhtml']));
		$this->assertEquals('red, green, & blue', Cleaner::String($this->fixture['entity']));
	}
	
	public function testCleanTrimmedStr()
	{
		$this->assertEquals('string', Cleaner::TrimmedString($this->fixture['str']));
		$this->assertEquals('trimmed string', Cleaner::TrimmedString($this->fixture['tstr']));
	}
	
	public function testCleanHTML()
	{
		$this->assertEquals('string', Cleaner::HTML($this->fixture['str']));
		$this->assertEquals('&lt;strong&gt;html&lt;/strong&gt;', Cleaner::HTML($this->fixture['html']));
		$this->assertEquals('&quo;double quoted string&quo;', Cleaner::HTML($this->fixture['dqstr']));
		$this->assertEquals("'single quoted string'", Cleaner::HTML($this->fixture['sqstr']));
		$this->assertEquals('&lt;strong&gt;html with &quo;double quotes&quo;&lt;/strong&gt;', Cleaner::HTML($this->fixture['dqHTML']));
		$this->assertEquals('red, green, & blue', Cleaner::HTML($this->fixture['entity']));
	}
	
	public function testCleanInt()
	{
		$this->assertEquals(0, Cleaner::Int($this->fixture['str']));
		$this->assertEquals(42, Cleaner::Int($this->fixture['int']));
		$this->assertEquals(3, Cleaner::Int($this->fixture['float']));
		$this->assertEquals(1, Cleaner::Int($this->fixture['bool1']));
	}
	
	public function testCleanFloat()
	{
		$this->assertEquals(0.0, Cleaner::Float($this->fixture['str']));
		$this->assertEquals(42.0, Cleaner::Float($this->fixture['int']));
		$this->assertEquals(3.14159, Cleaner::Float($this->fixture['float']));
	}
	
	public function testCleanBool()
	{
		$this->assertEquals(TRUE, Cleaner::Bool($this->fixture['bool1']));
		$this->assertEquals(TRUE, Cleaner::Bool($this->fixture['boolT']));
		$this->assertEquals(TRUE, Cleaner::Bool($this->fixture['boolY']));
		$this->assertEquals(FALSE, Cleaner::Bool($this->fixture['bool0']));
		$this->assertEquals(FALSE, Cleaner::Bool($this->fixture['boolF']));
		$this->assertEquals(FALSE, Cleaner::Bool($this->fixture['boolN']));
		$this->assertEquals(TRUE, Cleaner::Bool('    TRUE	'));
		$this->assertEquals(FALSE, Cleaner::Bool('	no       '));
	}
}
