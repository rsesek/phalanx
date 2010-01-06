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

    // The state of the current event. Be aware that the state of the event is
    // set BEFORE the specified method is called. This is used to avoid event
    // reentrancy.
    const NO_CURRENT_EVENT = 0;
    const EVENT_WILL_FIRE = 1;
    const EVENT_FIRE = 2;
    const EVENT_CLEANUP = 3;
    protected $current_event_state = self::NO_CURRENT_EVENT;

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
        // There is already an event executing. Push this new event into the
        // deferred worke queue.
        if ($this->current_event)
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
        $waiting_event       = $this->current_event;
        $waiting_event_state = $this->current_event_state;

        $this->_ProcessEvent($event);

        $this->current_event       = $waiting_event;
        $this->current_event_state = $waiting_event_state;

        $this->_DoDeferredEvents();
    }

    // This function does the bulk of the event processing work. This returns
    // TRUE if the event completed successfully, FALSE if otherwise.
    protected function _ProcessEvent(Event $event)
    {
        $this->current_event       = $event;
        $this->current_event_state = self::NO_CURRENT_EVENT;

        $this->current_event_state = self::EVENT_WILL_FIRE;
        $this->current_event->WillFire();

        // Make sure the event didn't get cancelled in WillFire().
        if ($this->current_event->is_cancelled())
        {
            $this->current_event->Cleanup();
            $this->current_event = NULL;
            return FALSE;
        }

        $this->current_event_state = self::EVENT_FIRE;
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
        $this->current_event_state = self::EVENT_CLEANUP;
        $this->current_event->Cleanup();

        $this->current_event_state = self::NO_CURRENT_EVENT;
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

    // Tells the pump to stop pumping events and to begin output handling. This
    // will call the current event's Cleanup() function.
    public function StopPump()
    {
        if ($this->current_event && $this->current_event_state < self::EVENT_CLEANUP)
        {
            $this->current_event_state = self::EVENT_CLEANUP;
            $this->current_event->Cleanup();
        }

        $this->output_handler->Start();
        $this->_Exit();
    }

    // Halts execution of the pump immediately without performing any event
    // cleanup. |$message| will be displayed as output.
    public function Terminate($message)
    {
        echo $message;
        $this->_Exit();
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
    // fired. If this is called while an event is being fired, it will be
    // included.
    public function GetEventChain()
    {
        $chain = $this->event_chain;
        if ($this->current_event && ($chain->IsEmpty() || $chain->Bottom() != $this->current_event))
        {
            $chain = clone $this->event_chain;
            $chain->Push($this->current_event);
        }
        return $chain;
    }

    // Internal wrapper around exit() that we can mock.
    protected function _Exit()
    {
        exit;
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
    static public function set_pump(EventPump $pump) { self::$pump = $pump; }

    public function set_output_handler(OutputHandler $handler) { $this->output_handler = $handler; }
    public function output_handler() { return $this->output_handler; }

    public function current_event_state() { return $this->current_event_state; }

    // Testing methods. These are not for public consumption.
    static public function T_set_pump($pump) { self::$pump = $pump; }
}

class EventPumpException extends \Exception
{
}
