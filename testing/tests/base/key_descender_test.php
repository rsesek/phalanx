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
use \phalanx\base\KeyDescender as KeyDescender;

class KeyDescenderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWithRef()
    {
        $array = array('foo' => 'bar');
        $desc = new KeyDescender($array);
        $array['foo'] = 'moo';

        $test = $desc->root();
        $this->assertEquals('moo', $test['foo']);
    }

    public function testGetSingleLevelArray()
    {
        $array = array('foo' => 'bar');
        $desc = new KeyDescender($array);
        $this->assertEquals('bar', $desc->get('foo'));
    }

    public function testGetSingleLevelObject()
    {
        $obj = new \stdClass();
        $obj->foo = 'bar';
        $desc = new KeyDescender($obj);
        $this->assertEquals('bar', $desc->get('foo'));
    }

    public function testGetTwoLevelArray()
    {
        $array = array(
            'foo' => array(
                'bar' => 'moo'
            )
        );
        $desc = new KeyDescender($array);
        $this->assertEquals('moo', $desc->get('foo.bar'));
    }

    public function testGetTwoLevelObject()
    {
        $obj = new \stdClass();
        $obj->foo = new \stdClass();
        $obj->foo->bar = 'moo';
        $desc = new KeyDescender($obj);
        $this->assertEquals('moo', $desc->get('foo.bar'));
    }

    public function testGetThreeLevelMixed()
    {
        $obj = new \stdClass();
        $obj->foo = array(
            'bar' => array(
                'moo' => new \stdClass()
            )
        );
        $obj->foo['bar']['moo']->cat = 'dog';
        $desc = new KeyDescender($obj);
        $this->assertEquals('dog', $desc->get('foo.bar.moo.cat'));
    }

    public function testGetBadObject()
    {
        $obj = new \stdClass();
        $obj->foo = 'moo';
        $desc = new KeyDescender($obj);
        $this->setExpectedException('phalanx\base\KeyDescenderException');
        $desc->get('foo.bar');
    }

    public function testGetUnsetObject()
    {
        $obj = new \stdClass();
        $obj->foo = new \stdClass();
        $desc = new KeyDescender($obj);
        $this->setExpectedException('phalanx\base\UndefinedKeyException');
        $desc->get('foo.bar');
    }

    public function testThrowExceptions()
    {
        $array = array();
        $desc = new KeyDescender($array);
        $desc->set_throw_undefined_errors(TRUE);
        $this->assertTrue($desc->throw_undefined_errors());
        $this->setExpectedException('phalanx\base\UndefinedKeyException');
        $desc->get('undefined.key');
    }

    public function testNoThrowExceptions()
    {
        $array = array();
        $desc = new KeyDescender($array);
        $desc->set_throw_undefined_errors(FALSE);
        $this->assertFalse($desc->throw_undefined_errors());
        $this->assertNull($desc->get('undefined.key'));
    }

    public function testGetSilent()
    {
        $array = array();
        $desc = new KeyDescender($array);
        $desc->set_throw_undefined_errors(TRUE);
        try
        {
            $this->assertNull($desc->getSilent('undefined_key'));
        }
        catch (\phalanx\base\UndefinedKeyException $e)
        {
            $this->fail('unexpected phalanx\base\UndefinedKeyException');
        }
    }

    public function testSetSingleLevelArray()
    {
        $array = array('foo' => 'bar');
        $desc = new KeyDescender($array);
        $desc->set('moo', 'cow');
        $this->assertEquals('cow', $desc->get('moo'));
    }

    public function testSetSingleLevelObject()
    {
        $obj = new \stdClass();
        $obj->foo = 'bar';
        $desc = new KeyDescender($obj);
        $desc->set('moo', 'cow');
        $this->assertEquals('cow', $desc->get('moo'));
    }

    public function testSetTwoLevelArray()
    {
        $array = array(
            'foo' => array(
                'bar' => 'moo'
            )
        );
        $desc = new KeyDescender($array);
        $desc->set('foo.red', 'blue');
        $this->assertEquals('blue', $desc->get('foo.red'));
    }

    public function testSetTwoLevelObject()
    {
        $obj = new \stdClass();
        $obj->foo = new \stdClass();
        $obj->foo->bar = 'moo';
        $desc = new KeyDescender($obj);
        $desc->set('foo.red', 'blue');
        $this->assertEquals('blue', $desc->get('foo.red'));
    }

    public function testSetInEmptyArray()
    {
        $array = array(
            'foo' => array(
            )
        );
        $desc = new KeyDescender($array);
        $desc->set('foo.test', 'abc');
        $this->assertEquals('abc', $desc->get('foo.test'));
    }

    public function testSetBad()
    {
        $array = array(
            'foo' => 'bar'
        );
        $desc = new KeyDescender($array);
        $this->setExpectedException('phalanx\base\KeyDescenderException');
        $desc->set('foo.test', 'moo');
    }

    public function testMagicGetter()
    {
        $array = array('foo' => array('moo' => 'boo'));
        $desc = new KeyDescender($array);
        $this->assertEquals('boo', $desc->{"foo.moo"});
        $this->assertSame($array['foo'], $desc->foo);
    }

    public function testMagicSetter()
    {
        $array = array('foo' => array('moo' => 'boo'));
        $desc = new KeyDescender($array);
        $desc->{"foo.moo"} = 'abc123';
        $this->assertEquals('abc123', $desc->get('foo.moo'));
    }

    public function testNestedKeyDescenders()
    {
        $array = array(
            'foo' => 'bar',
            'moo' => 'baz'
        );
        $outer = new KeyDescender($array);

        $inner = array(
            'abc' => 'def'
        );
        $outer->inner = new KeyDescender($inner);
        $this->assertEquals('def', $outer->Get('inner.abc'));
    }
}
