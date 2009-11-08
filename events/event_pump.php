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
// registered with the EventPump. The pump executes events as they come in, and
// the last non-cancelled event is usually the one whose output is rendered.
class EventPump
{
	// The shared event pump object.
	private static $pump;

	// A reference to the event currently being processed.
	protected $current_event = NULL;

    // An SplQueue of the events that were registered wtih PostEvent() but are
    // waiting for the current event to finish.
    protected $deferred_events = NULL;

	// An SplStack of all the events that have been Fire()d by the pump.
	protected $event_chain = NULL;

    // Constructor. Do not use directly. Use EventPump::Pump().
	public function __construct()
	{
		$this->deferred_events = new \SplQueue();
		$this->event_chain     = new \SplStack();
	}

    // Schedules an event to be run. If another event is currently being fired,
    // this will wait until that event is done. If no events are currently
    // running, the event will fire immediately.
    public function PostEvent(Event $event)
    {
    }

    // Preempts any currently executing event and preempts it with this event.
    // |$event| will begin processing immediately. The other event will
    // resume afterwards.
    public function RaiseEvent(Event $event)
    {
    }

    // Cancels the given Event and will begin processing the next deferred
    // event. If no other deferred events exist, output handling begins.
    public function Cancel(Event $event)
    {
    }

    // Calling this function will prevent any events registered with
    // PostEvent() from being run. A common use for this is registering an
    // event with RaiseEvent() and then stopping any future work from happening
    // using this method.
    public function CancelDeferredEvents()
    {
    }

    // This method will prevent any new events from registering with the pump
    // until a corresponding call to UnblockEvent() is made.
    public function BlockEvent()
    {
    }

    // Allow other events to register again after being blocked via
    // BlockEvent(). This must be called from within the Event that blocked.
    public function UnblockEvent()
    {
    }

    // Tells the pump to stop pumping events and to begin output handling. This
    // will call the current event's Cleanup() function.
    public function StopPump()
    {
    }

    // Halts execution of the pump immediately without performing any event
    // cleanup. |$message| will be displayed as output.
    public function Terminate($message)
    {
    }

    // Gets the currently executing Event.
    public function GetCurrentEvent()
    {
        return $this->current_event;
    }

    // Returns the queue of Events that have been registered with PostEvent()
    // and are waiting to run.
    public function GetDeferredEvents()
    {
        return $this->deferred_events;
    }

    // Returns the SplStack of events that have been fired, in the order they
    // fired.
    public function GetEventChain()
    {
        return $this->event_chain;
    }

	// Getters and setters.
	// -------------------------------------------------------------------------

	// Returns the shared EventPump.
	public function Pump()
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
