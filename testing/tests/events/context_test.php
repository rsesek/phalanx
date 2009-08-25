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

class ContextTest extends \PHPUnit_Framework_TestCase
{
	public $gpc_originals = array(
		'g' => array(),
		'p' => array(),
		'c' => array()
	);
	public $context;
	
	public function setUp()
	{
		if (!is_array($_GET))
			$_GET = array();
		if (!is_array($_POST))
			$_POST = array();
		if (!is_array($_COOKIE))
			$_COOKIE = array();
		
		$this->context = new TestContext();
		$this->gpc_originals['g'] = $_GET;
		$this->gpc_originals['p'] = $_POST;
		$this->gpc_originals['c'] = $_COOKIE;
	}
	
	public function tearDown()
	{
		$_GET = $this->gpc_originals['g'];
		$_POST = $this->gpc_originals['p'];
		$_COOKIE = $this->gpc_originals['c'];
	}
	
	public function testGPCInit()
	{
		$this->context->T_gpc()->set('p.foo', 'bar');
		$_POST['foo'] = 'moo';
		$gpc = $this->context->T_gpc();
		$this->assertEquals('bar', $gpc->get('p.foo'));
	}
	
	public function testEventWasHandled()
	{
		$pump = new events\EventPump();
		$context = new TestContext();
		$pump->set_context($context);
		$pump->raise(new TestEvent());
		$this->assertTrue($context->did_event_handled);
	}
	
	public function testBaseURL()
	{
		$this->assertEquals('/', $this->context->base_url());
		
		$this->context->set_base_url('/foo/bar');
		$this->assertEquals('/foo/bar/', $this->context->base_url());
		
		$this->context->set_base_url('/another/moo/');
		$this->assertEquals('/another/moo/', $this->context->base_url());
		
		$this->context->set_base_url('');
		$this->assertEquals('/', $this->context->base_url());
	}
	
	public function testTokenizeURLSimple()
	{
		$_GET['__dispatch__'] = '/test_event/';
		$context = new TestContext();
		$context->T_tokenizeURL();
		$this->assertEquals('test_event', $context->gpc()->get('g.' . TestContext::kEventNameKey));
	}
	
	public function testTokenizeURLWithID()
	{
		$_GET['__dispatch__'] = '/test/314159/';
		$context = new TestContext();
		$context->T_tokenizeURL();
		$this->assertEquals('test', $context->gpc()->get('g.' . TestContext::kEventNameKey));
		$this->assertEquals('314159', $context->gpc()->get('g.id'));
	}
	
	public function testTokenizeURLWith1Pair()
	{
		$_GET['__dispatch__'] = '/test_event/k1/v1/';
		$context = new TestContext();
		$context->T_tokenizeURL();
		$this->assertEquals('test_event', $context->gpc()->get('g.' . TestContext::kEventNameKey));
		$this->assertEquals('v1', $context->gpc()->get('g.k1'));
	}
	
	public function testTokenizeURLWith2Pair()
	{
		$_GET['__dispatch__'] = '/test_event/k1/v1/k2/v2/';
		$context = new TestContext();
		$context->T_tokenizeURL();
		$this->assertEquals('test_event', $context->gpc()->get('g.' . TestContext::kEventNameKey));
		$this->assertEquals('v1', $context->gpc()->get('g.k1'));
		$this->assertEquals('v2', $context->gpc()->get('g.k2'));
	}
	
	public function testTokenizeURLWithBadPair()
	{
		$_GET['__dispatch__'] = '/test_event/k1/';
		$context = new TestContext();
		$this->setExpectedException('\phalanx\events\ContextException');
		$context->T_tokenizeURL();
	}
	
	public function testTokenizeURLWithIDAndPair()
	{
		$_GET['__dispatch__'] = '/test_event/314159/k1/v1/';
		$context = new TestContext();
		$context->T_tokenizeURL();
		$this->assertEquals('test_event', $context->gpc()->get('g.' . TestContext::kEventNameKey));
		$this->assertEquals('314159', $context->gpc()->get('g.id'));
		$this->assertEquals('v1', $context->gpc()->get('g.k1'));
	}
	
	public function testDispatchGET()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_GET['__dispatch__'] = '/test_event/314159/k1/v1/k2/v2/';
		$pump = events\EventPump::pump();
		$context = new TestContext();
		$pump->set_context($context);
		$context->dispatch();
		$event = $pump->getLastEvent();
		$this->assertTrue($event->did_init);
		$this->assertTrue($event->did_handle);
		$this->assertTrue($event->did_end);
		$this->assertTrue($context->did_event_handled);
	}
	
	public function testDispatchPOST()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST[TestContext::kEventNameKey] = 'test_event';
		$pump = events\EventPump::pump();
		$context = new TestContext();
		$pump->set_context($context);
		$context->dispatch();
		$event = $pump->getLastEvent();
		$this->assertTrue($event->did_init);
		$this->assertTrue($event->did_handle);
		$this->assertTrue($event->did_end);
		$this->assertTrue($context->did_event_handled);
	}
	
	public function testSetEventClassLoader()
	{
		$closure = function($event_name) { return 'Load:' . $event_name; };
		$this->context->set_event_class_loader($closure);
		$set_closure = $this->context->event_class_loader();
		$this->assertEquals('Load:test', $set_closure('test'));
	}
	
	public function testSetViewLoader()
	{
		$event = new TestEvent();
		$event->test_prop = 'foo';
		$closure = function(events\Event $e) { return 'LoadView:' . $e->test_prop; };
		$this->context->set_view_loader($closure);
		$set_closure = $this->context->view_loader();
		$this->assertEquals('LoadView:foo', $set_closure($event));
	}
}
