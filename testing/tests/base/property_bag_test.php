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
use \phalanx\base as base;

require_once 'PHPUnit/Framework.php';

class PropertyBagTest extends \PHPUnit_Framework_TestCase
{
	public $bag;
	
	public function setUp()
	{
		$this->bag = new base\PropertyBag();
	}
	
	public function testSetProp()
	{
		$this->bag->foo = 'moo';
		$this->assertEquals('moo', $this->bag->foo);
	}
	
	public function testGetNullProp()
	{
		$this->assertNull($this->bag->foo);
	}
	
	public function testAllKeys()
	{
		$this->bag->foo = 'abc';
		$this->bag->moo = 'def';
		$this->bag->bar = 'hij';
		
		$keys = array('foo', 'moo', 'bar');
		$this->assertEquals($keys, $this->bag->allKeys());
	}
	
	public function testAllValues()
	{
		$this->bag->foo = 'abc';
		$this->bag->moo = 'def';
		$this->bag->bar = 'hij';
		
		$values = array('abc', 'def', 'hij');
		$this->assertEquals($values, $this->bag->allValues());
	}
	
	public function testToArray()
	{
		$this->bag->foo = 'abc';
		$this->bag->moo = 'def';
		$this->bag->bar = 'hij';
		
		$array = array(
			'foo' => 'abc',
			'moo' => 'def',
			'bar' => 'hij'
		);
		$this->assertEquals($array, $this->bag->toArray());
	}
	
	public function testHasKey()
	{
		$this->assertFalse($this->bag->hasKey('foo'));
		$this->bag->foo = 'moo';
		$this->assertTrue($this->bag->hasKey('foo'));
	}
	
	public function testContains()
	{
		$this->bag->foo = 'moo';
		$this->assertTrue($this->bag->contains('moo'));
		$this->assertFalse($this->bag->contains('foo'));
	}
}
