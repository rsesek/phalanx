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

class TestHTTPDispatcher extends tasks\HTTPInputFilter
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

class HTTPInputFilterTest extends \PHPUnit_Framework_TestCase
{
    // PHPUnit Configuration {{
        protected $backupGlobals = TRUE;
    // }}
    protected $filter;

    public function setUp()
    {
        $this->filter = new TestHTTPDispatcher('phalanx_action');
        unset($_SERVER['REQUEST_METHOD']);
        unset($_GET['__dispatch__']);
    }

    public function SetUrl($url)
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['__dispatch__'] = $url;
    }

    public function testCtor()
    {
        $this->assertEquals('phalanx_action', $this->filter->action_key());

        $this->filter = new TestHTTPDispatcher();
        $this->assertEquals('action', $this->filter->action_key());
    }

    public function testTokenizeURLSimple()
    {
        $this->SetUrl('/test_task/');
        $request = $this->filter->CreateRequest();
        $this->assertEquals('test_task', $request->action);
    }

    public function testTokenizeURLWithID()
    {
        $this->SetUrl('/test/314159/');
        $request = $this->filter->CreateRequest();
        $this->assertEquals('test', $request->action);
        $this->assertEquals('314159', $request->data->_id);
    }

    public function testTokenizeURLWith1Pair()
    {
        $this->SetUrl('/test_task/k1/v1/');
        $request = $this->filter->CreateRequest();
        $this->assertEquals('test_task', $request->action);
        $this->assertEquals('v1', $request->data->k1);
    }

    public function testTokenizeURLWith2Pair()
    {
        $this->SetUrl('/test_task/k1/v1/k2/v2/');
        $request = $this->filter->CreateRequest();
        $this->assertEquals('test_task', $request->action);
        $this->assertEquals('v1', $request->data->k1);
        $this->assertEquals('v2', $request->data->k2);
    }

    public function testTokenizeURLWithBadPair()
    {
        $this->SetUrl('/test_task/k1/v1/k2/');
        $this->setExpectedException('phalanx\tasks\HTTPInputFilterException');
        $this->filter->CreateRequest();
    }

    public function testGetTaskNameGET()
    {
        $this->SetUrl('/task.test/param1/value');
        $request = $this->filter->CreateRequest();
        $this->assertEquals('task.test', $request->action);
        $this->assertEquals('value', $request->data->param1);
    }

    public function testGetTaskNamePOSTWithURL()
    {
        $this->SetUrl('/task.post');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST[$this->filter->action_key()] = '-invalid-';
        $request = $this->filter->CreateRequest();
        $this->assertEquals('task.post', $request->action);
    }

    public function testGetTaskNamePOST()
    {
        $this->SetUrl('/');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST[$this->filter->action_key()] = 'task.post2';
        $request = $this->filter->CreateRequest();
        $this->assertEquals('task.post2', $request->action);
    }

    public function testGetTaskNameBad()
    {
        $this->SetUrl('/');
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->setExpectedException('phalanx\tasks\HTTPInputFilterException');
        $this->filter->CreateRequest();
    }

    public function testGetInputGET()
    {
        $this->SetUrl('/task.input/key1/foo/key2/bar/misc/baz/else/4');
        $request = $this->filter->CreateRequest();
        $this->assertEquals('foo', $request->data->key1);
        $this->assertEquals('bar', $request->data->key2);
        $this->assertEquals('GET', $request->data->_method);
    }

    public function testGetInputPOST()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'key1' => 'foo',
            'key2' => 'bar',
        );
        $request = $this->filter->CreateRequest();
        $this->assertEquals('foo', $request->data->key1);
        $this->assertEquals('bar', $request->data->key2);
        $this->assertEquals('POST', $request->data->_method);
    }

    public function testGetInputGETMissingKey()
    {
        $this->SetUrl('/task.bad/key1/foo/');
        $request = $this->filter->CreateRequest();
        $this->assertEquals('foo', $request->data->key1);
        $this->assertEquals('GET', $request->data->_method);
        $this->assertNull($request->data->key2);
    }

    public function testGetInputPOSTMissingKey()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['key1'] = 'foo';
        $request = $this->filter->CreateRequest();
        $this->assertEquals('foo', $request->data->key1);
        $this->assertEquals('POST', $request->data->_method);
        $this->assertNull($request->data->key2);
    }
}
