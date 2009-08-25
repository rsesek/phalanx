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
	// The POST variable name used to determine the event to raise.
	const kEventPOSTVarKey = 'phalanx_event';
	
	// GPC variables. By default these are unsanitized. On construction, the
	// variable arrays are copied from their respective superglobals.
	protected $gpc = null;
	
	// The base URL. This is stripped from the URL before tokenizing it in a
	// GET request.
	protected $base_url = '/';
	
	public function __construct()
	{
		$gpc = array(
			'g' => (array)$_GET,
			'p' => (array)$_POST,
			'c' => (array)$_COOKIE
		);
		$this->gpc = new \phalanx\base\KeyDescender($gpc);
	}
	
	// This raises events based on incoming GET and POST data. If it's a GET
	// request, the URL will be used to determine the event. If the request is
	// a POST one, the key |kEventPOSTVarKey| will be used to determine the
	// event. If neither one of those works, an exception is thrown.
	public function dispatch()
	{
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		if ($method == 'post')
		{
			$event_name = $this->gpc->get('p.' . self::kEventPOSTVarKey);
		}
		else if ($method == 'get')
		{
			$this->_tokenizeURL();
			$event_name = $this->gpc->get('g.' . self::kEventPOSTVarKey);
		}
		else
		{
			throw new ContextException("Unknown HTTP method '$method'");
		}
		
		$event_name = \phalanx\base\underscore_to_cammelcase($event_name);
		if (class_exists($event_name . 'Event'))
			$event_name .= 'Event';
		else if (!class_exists($event_name))
			throw new ContextException("Unable to locate event class for '$event_name'");
		
		$event = new $event_name();
		$event->set_context($this);
		EventPump::pump()->raise($event);
	}
	
	// This splits a request URL into the event name and then appropriate key
	// value matching. URLs can take the form:
	//   /event_name/id
	//   /event_name/id/k1/v1/k2/v2/
	//   /event_name/k1/v1/k2/v2/
	// The differentiation of |id| vs |k1| after |event_name| depends on if
	// that path component is numeric.
	protected function _tokenizeURL()
	{
		$url = $this->gpc->get('g.__dispatch__');
		$base_url_pattern = preg_quote($this->base_url, '/');
		$url = preg_replace('/^' . $base_url_pattern . '/', '', $url);
		$parts = explode('/', $url);
		\phalanx\base\array_strip_empty($parts);
		
		$this->gpc->set('g.' . self::kEventPOSTVarKey, $parts[0]);
		
		$i = 1;
		if (is_numeric($parts[$i]))
			$this->gpc->set('g.id', $parts[$i++]);
		
		for ( ; $i < count($parts); $i += 2)
		{
			if (!isset($parts[$i]) || !isset($parts[$i+1]))
				throw new ContextException("Invalid key-value pair in URL '$url'");
			$this->gpc->set('g.' . $parts[$i], $parts[$i+1]);
		}
	}
	
	// Called by the EventPump when an event in this context has been handled
	// successfully and is ready for context-specific handling.
	public function onEventHandled(Event $event)
	{
	}
	
	// Setters and getters.
	// -------------------------------------------------------------------------
	public function gpc() { return $this->gpc; }
	
	public function set_base_url($url)
	{
		if ($url[strlen($url) - 1] != '/')
			$url .= '/';
		$this->base_url = $url;
	}
	public function base_url() { return $this->base_url; }
}

class ContextException extends \Exception
{
}
