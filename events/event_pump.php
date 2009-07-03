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

// User interaction and other functions produce events, which are raised and
// registered with the EventPump. It buffers output and determines the final
// output to render based on the event stack.
class EventPump
{
	// The shared event pump object.
	private static $pump;
	
	// Index of the current event.
	protected $current_event = -1;
	
	// An array of all the events that have been registered with the pump. This
	// is a stack.
	protected $events;
	
	// An array of output from the different events. This is indexed
	// symmetrically with |$this->events|.
	protected $events_output;
	
	// The current Context that events take place in.
	protected $context;
	
	public function __construct()
	{
		$this->events = array();
		$this->events_output = array();
	}
	
	// Adds an event to the pump. Checks to see if the event can be handled
	// and, if so, runs the handler.
	public function raise(Event $event)
	{
		if (!$this->context)
			throw new EventPumpException('No valid \phalanx\events\Context present.');
		
		if (!$event::canRunInContext($this->context))
			return;
		
		array_push($this->events, $event);
		array_push($this->events_output, '');
		
		$event->set_context($this->context);
		$event->init();
		
		$this->current_event++;
		
		$event->handle();
		
		$event->end();
	}
	
	// Returns the last-raised Event.
	public function getLastEvent()
	{
		return $this->events[sizeof($this->events) - 1];
	}
	
	// Returns the Event that is currently being handled. This is different from
	// the last-raised Event in that events can be cancelled in the init() stage.
	public function getCurrentEvent()
	{
		return $this->events[$this->current_event];
	}
	
	// Getters and setters.
	// --------------------------------------------------------------------------
	public function set_context(Context $context) { $this->context = $context; }
	public function context() { return $this->context; }
	
	// Returns the shared EventPump.
	public function pump()
	{
		if (!self::$pump)
			self::set_pump(new EventPump());
		return self::$pump;
	}
	public static function set_pump(EventPump $pump) { self::$pump = $pump; }
	
	// Testing methods. These are not for public consumption.
	public static function T_set_pump($pump) { self::$pump = $pump; }
}

class EventPumpException extends \Exception
{
}
