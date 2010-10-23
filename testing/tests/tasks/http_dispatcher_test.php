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
use \phalanx\tasks as tasks;

require_once 'PHPUnit/Framework.php';

class TestHTTPDispatcher extends tasks\HTTPDispatcher
{
    public function T_set_request_method($m) { $this->request_method = $m; }
    public function T_set_url_input($i) { $this->url_input = $i; }
    public function T_TokenizeURL($url)
    {
        return $this->_TokenizeURL($url);
    }
    public function T_GetTaskName()
    {
        return $this->_GetTaskName();
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
        $this->assertEquals('ename', $this->dispatcher->task_input_key());

        $this->dispatcher = new TestHTTPDispatcher();
        $this->assertEquals('phalanx_task', $this->dispatcher->task_input_key());
    }

    public function testTokenizeURLSimple()
    {
        $url = '/test_task/';
        $params = $this->dispatcher->T_TokenizeURL($url);
        $this->assertEquals('test_task', $params->_task);
    }

    public function testTokenizeURLWithID()
    {
        $url = '/test/314159/';
        $params = $this->dispatcher->T_TokenizeURL($url);
        $this->assertEquals('test', $params->_task);
        $this->assertEquals('314159', $params->_id);
    }

    public function testTokenizeURLWith1Pair()
    {
        $url = '/test_task/k1/v1/';
        $params = $this->dispatcher->T_TokenizeURL($url);
        $this->assertEquals('test_task', $params->_task);
        $this->assertEquals('v1', $params->k1);
    }

    public function testTokenizeURLWith2Pair()
    {
        $url = '/test_task/k1/v1/k2/v2/';
        $params = $this->dispatcher->T_TokenizeURL($url);
        $this->assertEquals('test_task', $params->_task);
        $this->assertEquals('v1', $params->k1);
        $this->assertEquals('v2', $params->k2);
    }

    public function testTokenizeURLWithBadPair()
    {
        $url = '/test_task/k1/v1/k2/';
        $this->setExpectedException('phalanx\tasks\HTTPDispatcherException');
        $this->dispatcher->T_TokenizeURL($url);
    }

    public function testGetTaskNameGET()
    {
        $this->dispatcher->T_set_request_method('GET');
        $input = $this->dispatcher->T_TokenizeURL('/task.test/param1/value');
        $this->dispatcher->T_set_url_input($input);
        $this->assertEquals('task.test', $this->dispatcher->T_GetTaskName());
    }

    public function testGetTaskNamePOSTWithURL()
    {
        $this->dispatcher->T_set_request_method('POST');
        $_POST[$this->dispatcher->task_input_key()] = '-invalid-';
        $input = $this->dispatcher->T_TokenizeURL('/task.post');
        $this->dispatcher->T_set_url_input($input);
        $this->assertEquals('task.post', $this->dispatcher->T_GetTaskName());
    }

    public function testGetTaskNamePOST()
    {
        $this->dispatcher->T_set_request_method('POST');
        $_POST[$this->dispatcher->task_input_key()] = 'task.post2';
        $input = $this->dispatcher->T_TokenizeURL('/');
        $this->dispatcher->T_set_url_input($input);
        $this->assertEquals('task.post2', $this->dispatcher->T_GetTaskName());
    }

    public function testGetTaskNameBad()
    {
        $this->dispatcher->T_set_request_method('PUT');
        $input = $this->dispatcher->T_TokenizeURL('/');
        $this->dispatcher->T_set_url_input($input);
        $this->setExpectedException('phalanx\tasks\DispatcherException');
        $this->dispatcher->Start();
    }

    public function testGetInputGET()
    {
        $_GET['key1'] = '-invalid-';
        $this->dispatcher->T_set_request_method('GET');
        $input = $this->dispatcher->T_TokenizeURL('/task.input/key1/foo/key2/bar/misc/baz/else/4');
        $this->dispatcher->T_set_url_input($input);
        $gathered_input = $this->dispatcher->T_GetInput(TestTask::InputList());
        $this->assertEquals(3, $gathered_input->Count());
        $this->assertEquals('foo', $gathered_input->key1);
        $this->assertEquals('bar', $gathered_input->key2);
        $this->assertEquals('GET', $gathered_input->_method);
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
        $gathered_input= $this->dispatcher->T_GetInput(TestTask::InputList());
        $this->assertEquals(3, $gathered_input->Count());
        $this->assertEquals('foo', $gathered_input->key1);
        $this->assertEquals('bar', $gathered_input->key2);
        $this->assertEquals('POST', $gathered_input->_method);
    }

    public function testGetInputBadRequest()
    {
        $this->dispatcher->T_set_request_method('PUT');
        $this->setExpectedException('phalanx\tasks\HTTPDispatcherException');
        $this->dispatcher->T_GetInput(TestTask::InputList());
    }

    public function testGetInputGETMissingKey()
    {
        $this->dispatcher->T_set_request_method('GET');
        $input = $this->dispatcher->T_TokenizeURL('/task.bad/key1/foo/');
        $this->dispatcher->T_set_url_input($input);
        $gathered_input = $this->dispatcher->T_GetInput(TestTask::InputList());
        $this->assertEquals(2, $gathered_input->Count());
        $this->assertEquals('foo', $gathered_input->key1);
        $this->assertEquals('GET', $gathered_input->_method);
        $this->assertNull($gathered_input->key2);
    }

    public function testGetInputPOSTMissingKey()
    {
        $_POST['key1'] = 'foo';
        $this->dispatcher->T_set_request_method('POST');
        $gathered_input = $this->dispatcher->T_GetInput(TestTask::InputList());
        $this->assertEquals(2, $gathered_input->Count());
        $this->assertEquals('foo', $gathered_input->key1);
        $this->assertEquals('POST', $gathered_input->_method);
        $this->assertNull($gathered_input->key2);
    }
}
