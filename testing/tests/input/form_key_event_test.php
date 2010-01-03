<?php
// Phalanx
// Copyright (c) 2009-2010 Blue Static
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

class FormKeyEventTest extends \PHPUnit_Framework_TestCase
{
    public $event;
    public $pump;
    public $form_key;

    public function setUp()
    {
        $this->pump = new \phalanx\events\EventPump();
        $this->form_key = new input\FormKeyManager(new TestFormKeyManagerDelegate());
        $this->event = new input\ValidateFormKeyEvent($this->form_key);
    }

    public function testCtor()
    {
        $this->assertAttributeSame($this->form_key, 'manager', $this->event);
    }

    public function testNoInput()
    {
        $this->pump->PostEvent($this->event);
        $this->assertTrue($this->event->is_cancelled());
    }

    public function testGETKey()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['phalanx_form_key'] = 'foo';
        $this->pump->PostEvent($this->event);
        $this->assertTrue($this->event->is_cancelled());
    }

    public function testInputList()
    {
        $this->assertEquals(array('phalanx_form_key'), input\ValidateFormKeyEvent::InputList());
    }

    public function testOutputList()
    {
        $this->assertNull(input\ValidateFormKeyEvent::OutputList());
    }

    public function testInvalidPOST()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['phalanx_form_key'] = 'foo';

        $this->setExpectedException('phalanx\input\FormKeyException');
        $this->pump->PostEvent($this->event);

        $this->assertFalse($this->event->is_cancelled());
        $this->assertTrue($this->form_key->delegate()->did_get);
    }

    public function testGoodKey()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['phalanx_form_key'] = $this->form_key->Generate();

        $this->pump->PostEvent($this->event);

        $this->assertFalse($this->event->is_cancelled());
        $this->assertTrue($this->form_key->delegate()->did_get);
    }
}
