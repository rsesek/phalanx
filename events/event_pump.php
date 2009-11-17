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

    // The OutputHandler instance for the pump.
    protected $output_handler = NULL;

	// A reference to the event currently being processed.
	protected $current_event = NULL;

    // An SplQueue of the events that were registered wtih PostEvent() but are
    // waiting for the current event to finish.
    protected $deferred_events = NULL;

	// An SplStack of all the events that have been Fire()d by the pump.
	protected $event_chain = NULL;

    // The name of the event that is currently blocking.
    protected $blocking_event = NULL;

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
        // There is already an event executing. Push this new event into the
        // deferred worke queue.
        if ($this->current_event || $this->blocking_event)
        {
            $this->deferred_events->Push($event);
            return;
        }

        $this->_ProcessEvent($event);

        $this->_DoDeferredEvents();
    }

    // Preempts any currently executing event and preempts it with this event.
    // |$event| will begin processing immediately. The other event will
    // resume afterwards.
    public function RaiseEvent(Event $event)
    {
        if ($this->blocking_event)
        {
            $this->deferred_events->Push($event);
            return;
        }

        $waiting_event = $this->current_event;
        $this->_ProcessEvent($event);
        $this->current_event = $waiting_event;
        $this->_DoDeferredEvents();
    }

    // This function does the bulk of the event processing work. This returns
    // TRUE if the event completed successfully, FALSE if otherwise.
    protected function _ProcessEvent(Event $event)
    {
        $this->current_event = $event;
        $this->current_event->WillFire();

        // Make sure the event didn't get cancelled in WillFire().
        if ($this->current_event->is_cancelled())
        {
            $this->current_event->Cleanup();
            $this->current_event = NULL;
            return FALSE;
        }

        $this->current_event->Fire();

        // Make sure the event didn't get cancelled in Fire().
        if ($this->current_event->is_cancelled())
        {
            $this->current_event->Cleanup();
            $this->current_event = NULL;
            return FALSE;
        }

        // The event successfully executed, so add it to the event chain.
        $this->event_chain->Push($this->current_event);
        $this->current_event->Cleanup();
        $this->current_event = NULL;
        return TRUE;
    }

    // If there are no events currently processing, this will process all the
    // events in the deferred queue.
    protected function _DoDeferredEvents()
    {
        if ($this->current_event)
            return;

        while ($this->deferred_events->Count() > 0)
            $this->_ProcessEvent($this->deferred_events->Pop());
    }

    // Cancels the given Event and will begin processing the next deferred
    // event. If no other deferred events exist, output handling begins.
    public function Cancel(Event $event)
    {
        $event->set_cancelled();
    }

    // Calling this function will prevent any events registered with
    // PostEvent() from being run. A common use for this is registering an
    // event with RaiseEvent() and then stopping any future work from happening
    // using this method.
    public function CancelDeferredEvents()
    {
        while ($this->deferred_events->Count() > 0)
            $this->deferred_events->Shift();
    }

    // This method will prevent any new events from registering with the pump
    // until a corresponding call to UnblockEvent() is made.
    public function BlockEvent()
    {
        if ($this->blocking_event)
            throw new EventPumpException('EventPump is already blocked by ' . $this->blocking_event);

        $event = $this->_GetCallingEvent();
        if (!$event)
            throw new EventPumpException('Cannot block events while not in the context of one');

        $this->blocking_event = $event;
    }

    // Allow other events to register again after being blocked via
    // BlockEvent(). This must be called from within the Event that blocked.
    public function UnblockEvent()
    {
        if (!$this->blocking_event)
            throw new EventPumpException('EventPump is not blocked');

        $event = $this->_GetCallingEvent();
        if ($event != $this->blocking_event)
            throw new EventPumpException('EventPump is blocked by ' . $this->blocking_event .
                                         ', not ' . $event);

        $this->blocking_event = NULL;
        $this->_DoDeferredEvents();
    }

    // This examines the stack trace to locate the calling functions.
    protected function _GetCallingEvent()
    {
        $trace = debug_backtrace();
        foreach ($trace as $frame)
            if (isset($frame['class']) && is_subclass_of($frame['class'], 'phalanx\events\Event'))
                return $frame['class'];
        return NULL;
    }

    // Tells the pump to stop pumping events and to begin output handling. This
    // will call the current event's Cleanup() function.
    public function StopPump()
    {
        $this->current_event->Cleanup();
        $this->output_handler->Start();
        exit;
    }

    // Halts execution of the pump immediately without performing any event
    // cleanup. |$message| will be displayed as output.
    public function Terminate($message)
    {
        echo $message;
        exit;
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

    public function set_output_handler(OutputHandler $handler) { $this->output_handler = $handler; }
    public function output_handler() { return $this->output_handler; }

	// Testing methods. These are not for public consumption.
	public static function T_set_pump($pump) { self::$pump = $pump; }
}

class EventPumpException extends \Exception
{
}
