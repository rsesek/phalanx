<?php
// Phalanx
// Copyright (c) 2011 Blue Static
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

require_once TEST_ROOT . '/tests/base.php';

class ABCTester extends \phalanx\base\StrictObject
{
    public $a = 1;
    public $b = NULL;
    public $c = '';
}

class StrictObjectTest extends \PHPUnit_Framework_TestCase
{
    public $object;

    public function setUp()
    {
        $this->object = new ABCTester();
    }

    public function testGetA()
    {
        $this->assertSame(1, $this->object->a);
    }

    public function testGetB()
    {
        $this->assertSame(NULL, $this->object->b);
    }

    public function testGetC()
    {
        $this->assertSame('', $this->object->c);
    }

    public function testGetD()
    {
        $this->setExpectedException('\phalanx\base\StrictObjectException');
        $this->assertNull($this->object->d);
    }

    public function testSetA()
    {
        $this->object->a = NULL;
        $this->assertSame(NULL, $this->object->a);
    }

    public function testSetB()
    {
        $this->object->b = '';
        $this->assertSame('', $this->object->b);
    }

    public function testSetC()
    {
        $this->object->c = 2;
        $this->assertSame(2, $this->object->c);
    }

    public function testSetD()
    {
        $this->setExpectedException('\phalanx\base\StrictObjectException');
        $this->object->d = 'foo';
        $this->assertNull($this->object->d);
    }

    public function testGetException()
    {
        try {
            $d = $this->object->d;
        } catch (\phalanx\base\StrictObjectException $e) {
            $this->assertFalse(strpos($e->GetMessage(), 'StrictObject'));
            $this->assertInternalType('int', strpos($e->GetMessage(), 'ABCTester::d'));
        }
    }

    public function testSetException()
    {
        try {
            $this->object->d = 3;
        } catch (\phalanx\base\StrictObjectException $e) {
            $this->assertFalse(strpos($e->GetMessage(), 'StrictObject'));
            $this->assertInternalType('int', strpos($e->GetMessage(), 'ABCTester::d'));
        }
    }
}
