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

namespace phalanx\events;

// A context object is used to store information about state and the HTTP
// request (GPC variables). Events are handleded within a specific context.
class Context
{
	// GPC variables. By default these are unsanitized. On construction, the
	// variable arrays are copied from their respective superglobals.
	protected $gpc = null;
	
	public function __construct()
	{
		$gpc = array(
			'g' => (array)$_GET,
			'p' => (array)$_POST,
			'c' => (array)$_COOKIE
		);
		$this->gpc = new \phalanx\base\KeyDescender($gpc);
	}
	
	// Called by the EventPump when an event in this context has been handled
	// successfully and is ready for context-specific handling.
	public function onEventHandled(Event $event)
	{
	}
	
	// Setters and getters.
	// -------------------------------------------------------------------------
	public function gpc() { return $this->gpc; }
}
