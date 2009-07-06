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
		$this->context = new events\Context();
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
		$this->context->T_set_gpc_var('p', 'foo', 'bar');
		$_POST['foo'] = 'moo';
		$gpc = $this->context->T_gpc();
		$this->assertEquals('bar', $gpc['p']['foo']);
	}
}
