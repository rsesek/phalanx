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
use \phalanx\events as events;

require_once 'PHPUnit/Framework.php';

class TestHTTPDispatcher extends events\HTTPDispatcher
{
    public function T_set_request_method($m) { $this->request_method = $m; }
    public function T_set_url_input($i) { $this->url_input = $i; }
    public function T_TokenizeURL($url)
    {
        return $this->_TokenizeURL($url);
    }
    public function T_GetEventName()
    {
        return $this->_GetEventName();
    }
    public function T_GetInput(Array $keys)
    {
        return $this->_GetInput($keys);
    }
}

class HTTPDispatcherTest extends \PHPUnit_Framework_TestCase
{
    // PHPUnit Configuration {{
        protected $backupGlobals = TRUE;
    // }}
    protected $dispatcher;

    public function setUp()
    {
        $this->dispatcher = new TestHTTPDispatcher('ename');
    }

    public function testCtor()
    {
        $this->assertEquals('ename', $this->dispatcher->event_input_key());

        $this->dispatcher = new TestHTTPDispatcher();
        $this->assertEquals('phalanx_event', $this->dispatcher->event_input_key());
    }

    public function testTokenizeURLSimple()
    {
        $url = '/test_event/';
        $params = $this->dispatcher->T_TokenizeURL($url);
        $this->assertEquals('test_event', $params->_event);
    }

    public function testTokenizeURLWithID()
    {
        $url = '/test/314159/';
        $params = $this->dispatcher->T_TokenizeURL($url);
        $this->assertEquals('test', $params->_event);
        $this->assertEquals('314159', $params->_id);
    }

    public function testTokenizeURLWith1Pair()
    {
        $url = '/test_event/k1/v1/';
        $params = $this->dispatcher->T_TokenizeURL($url);
        $this->assertEquals('test_event', $params->_event);
        $this->assertEquals('v1', $params->k1);
    }

    public function testTokenizeURLWith2Pair()
    {
        $url = '/test_event/k1/v1/k2/v2/';
        $params = $this->dispatcher->T_TokenizeURL($url);
        $this->assertEquals('test_event', $params->_event);
        $this->assertEquals('v1', $params->k1);
        $this->assertEquals('v2', $params->k2);
    }

    public function testTokenizeURLWithBadPair()
    {
        $url = '/test_event/k1/v1/k2/';
        $this->setExpectedException('phalanx\events\HTTPDispatcherException');
        $this->dispatcher->T_TokenizeURL($url);
    }

    public function testGetEventNameGET()
    {
        $this->dispatcher->T_set_request_method('GET');
        $input = $this->dispatcher->T_TokenizeURL('/event.test/param1/value');
        $this->dispatcher->T_set_url_input($input);
        $this->assertEquals('event.test', $this->dispatcher->T_GetEventName());
    }

    public function testGetEventNamePOSTWithURL()
    {
        $this->dispatcher->T_set_request_method('POST');
        $_POST[$this->dispatcher->event_input_key()] = '-invalid-';
        $input = $this->dispatcher->T_TokenizeURL('/event.post');
        $this->dispatcher->T_set_url_input($input);
        $this->assertEquals('event.post', $this->dispatcher->T_GetEventName());
    }

    public function testGetEventNamePOST()
    {
        $this->dispatcher->T_set_request_method('POST');
        $_POST[$this->dispatcher->event_input_key()] = 'event.post2';
        $input = $this->dispatcher->T_TokenizeURL('/');
        $this->dispatcher->T_set_url_input($input);
        $this->assertEquals('event.post2', $this->dispatcher->T_GetEventName());
    }

    public function testGetEventNameBad()
    {
        $this->dispatcher->T_set_request_method('PUT');
        $input = $this->dispatcher->T_TokenizeURL('/');
        $this->dispatcher->T_set_url_input($input);
        $this->setExpectedException('phalanx\events\HTTPDispatcherException');
        $this->dispatcher->T_GetEventName();
    }

    public function testGetInputGET()
    {
        $_GET['key1'] = '-invalid-';
        $this->dispatcher->T_set_request_method('GET');
        $input = $this->dispatcher->T_TokenizeURL('/event.input/key1/foo/key2/bar/misc/baz/else/4');
        $this->dispatcher->T_set_url_input($input);
        $gathered_input = $this->dispatcher->T_GetInput(TestEvent::InputList());
        $this->assertEquals(2, $gathered_input->Count());
        $this->assertEquals('foo', $gathered_input->key1);
        $this->assertEquals('bar', $gathered_input->key2);
    }

    public function testGetInputPOST()
    {
        $_POST = array(
            'key1' => 'foo',
            'key2' => 'bar',
            'misc' => 'baz',
            'else' => 4,
        );
        $this->dispatcher->T_set_request_method('POST');
        $gathered_input= $this->dispatcher->T_GetInput(TestEvent::InputList());
        $this->assertEquals(2, $gathered_input->Count());
        $this->assertEquals('foo', $gathered_input->key1);
        $this->assertEquals('bar', $gathered_input->key2);
    }

    public function testGetInputBadRequest()
    {
        $this->dispatcher->T_set_request_method('PUT');
        $this->setExpectedException('phalanx\events\HTTPDispatcherException');
        $this->dispatcher->T_GetInput(TestEvent::InputList());
    }

    public function testGetInputGETMissingKey()
    {
        $this->dispatcher->T_set_request_method('GET');
        $input = $this->dispatcher->T_TokenizeURL('/event.bad/key1/foo/');
        $this->dispatcher->T_set_url_input($input);
        $gathered_input = $this->dispatcher->T_GetInput(TestEvent::InputList());
        $this->assertEquals(1, $gathered_input->Count());
        $this->assertEquals('foo', $gathered_input->key1);
        $this->assertNull($gathered_input->key2);
    }

    public function testGetInputPOSTMissingKey()
    {
        $_POST['key1'] = 'foo';
        $this->dispatcher->T_set_request_method('POST');
        $gathered_input = $this->dispatcher->T_GetInput(TestEvent::InputList());
        $this->assertEquals(1, $gathered_input->Count());
        $this->assertEquals('foo', $gathered_input->key1);
        $this->assertNull($gathered_input->key2);
    }
}
