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

require_once TEST_ROOT . '/tests/input.php';

class FormKeyTest extends \PHPUnit_Framework_TestCase
{
    public $manager = NULL;
    public $delegate = NULL;

    public function setUp()
    {
        $this->delegate = new TestFormKeyManagerDelegate();
        $this->manager = new input\FormKeyManager($this->delegate);
    }

    public function testSetDelegate()
    {
        $this->assertNotNull($this->manager->delegate());

        $this->manager->set_delegate(new TestFormKeyManagerDelegate());
        $this->assertNotSame($this->manager->delegate(), $this->delegate);
    }

    public function testGenerateRandom()
    {
        $keys = array();
        for ($i = 0; $i < 100; $i++)
        {
            $key = $this->manager->Generate();
            if (isset($keys[$key]))
                $this->fail("nonrandom key '$key' detected at iteration $i");
            $keys[$key] = $key;
        }
    }

    public function testGenerateSave()
    {
        $key = $this->manager->Generate();
        $this->assertEquals($key, $this->delegate->key_storage[$key]->key);
    }

    public function testIsValid()
    {
        $start = time();
        $key = $this->manager->Generate();
        $this->assertTrue($this->manager->IsValid($key));
        $this->assertTrue($this->delegate->did_get);
        $form_key = $this->delegate->key_storage[$key];
        $this->assertLessThanOrEqual(time(), $form_key->timestamp);
        $this->assertGreaterThan(time() - 10, $form_key->timestamp);
    }

    public function testIsNotValidNoExist()
    {
        $this->assertFalse($this->manager->IsValid('INVALID KEY'));
        $this->assertTrue($this->delegate->did_get);
    }

    public function testIsNotValidTimeExpire()
    {
        $key = $this->manager->Generate();
        $this->delegate->key_storage[$key]->timestamp = time() - 9999;
        $this->assertFalse($this->manager->IsValid($key));
    }

    public function testGenerateHTML()
    {
        $html = $this->manager->GenerateHTML();
        $keys = array_keys($this->delegate->key_storage);
        $name = 'phalanx_form_key';
        $this->assertEquals('<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $keys[0] . '" />', $html);
    }

    public function testValidateIsValid()
    {
        $key = $this->manager->Generate();
        $this->assertEquals($key, $this->delegate->key_storage[$key]->key);
        $this->assertTrue($this->manager->Validate($key));
        $this->assertTrue($this->delegate->did_get);
        $this->assertFalse(isset($this->delegate->key_storage[$key]));
    }

    public function testValidateInvalidTime()
    {
        $key = $this->manager->Generate();
        $this->delegate->key_storage[$key]->timestamp = time() - 9999;
        $this->assertEquals($key, $this->delegate->key_storage[$key]->key);
        $this->assertFalse($this->manager->Validate($key));
        $this->assertFalse(isset($this->delegate->key_storage[$key]));
    }

    public function testValidateInvalidKey()
    {
        $this->assertEquals(0, count($this->delegate->key_storage));
        $this->assertFalse($this->manager->Validate('INVALID KEY'));
        $this->assertEquals(0, count($this->delegate->key_storage));
    }

    public function testInvalidate()
    {
        $key = $this->manager->Generate();
        $this->assertEquals($key, $this->delegate->key_storage[$key]->key);
        $this->manager->Invalidate($key);
        $this->assertTrue($this->delegate->did_delete);
        $this->assertFalse(isset($this->delegate->key_storage[$key]));
    }
}
