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
use \phalanx\base as base;

require_once 'PHPUnit/Framework.php';

class TestStruct extends base\Struct
{
    protected $fields = array(
        'first',
        'second',
        'third'
    );
}

class StructTest extends \PHPUnit_Framework_TestCase
{
    public $struct;

    public function setUp()
    {
        $this->struct = new TestStruct();
    }

    public function testCount()
    {
        $this->assertEquals(3, $this->struct->Count());
        $this->struct->first = 'foo';
        $this->assertEquals(3, $this->struct->Count());        
    }

    public function testSet()
    {
        $this->struct->first  = 1;
        $this->struct->second = 2;
        $this->struct->third  = 3;
    }

    public function testSetInvalid()
    {
        $this->setExpectedException('phalanx\base\StructException');
        $this->struct->fourth = 4;
    }

    public function testGetNull()
    {
        $this->assertNull($this->struct->first);
        $this->assertNull($this->struct->second);
        $this->assertNull($this->struct->third);
    }

    public function testGet()
    {
        $this->struct->first = 1;
        $this->assertEquals(1, $this->struct->first);
    }

    public function testGetInvalid()
    {
        $this->setExpectedException('phalanx\base\StructException');
        $foo = $this->struct->fourth;
    }

    public function testToArray()
    {
        $this->struct->first = 'alpha';
        $this->struct->third = 'bravo';
        $array = array(
            'first' => 'alpha',
            'third' => 'bravo'
        );
        $this->assertEquals($array, $this->struct->ToArray());
    }
}
