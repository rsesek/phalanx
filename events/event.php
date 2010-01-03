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

// A base representation of an event. Events should not generate any output,
// rather they should store data in member variables, which can then be used by
// the view system.
abstract class Event
{
    // The input passed to the event upon creation.
    protected $input = NULL;

    // Whether or not the event is cancelled.
    private $cancelled = FALSE;

    // Creates an instance of the Event class. The PropertyBag of input is
    // assembled for the Event by the Dispatcher. It collects input variables
    // based on the keys the Event asks for via the InputList() method.
    public function __construct(\phalanx\base\PropertyBag $input = NULL)
    {
        $this->input = $input;
    }

    // Returns an array of input keys the Event requires in order to perform
    // its work. Returning NULL means this Event requires no input.
    abstract static public function InputList();

    // Returns an array of keys that exist on this Event class that the
    // OutputHandler can access. NULL for no output.
    abstract static public function OutputList();

    // Called before the EventPump is preparing to Fire() the event. This is a
    // good place to put permission and general sanity checks.
    public function WillFire() {}

    // The actual processing work of the Event happens in Fire(). As the Event
    // generates output, it should put it into the properties it declared in
    // OutputList().
    abstract public function Fire();

    // Called after the EventPump is done with the Event. This will be called
    // even if the Event is preempted by another and this one does not Fire().
    public function Cleanup() {}

    // Cancels the current event. Cleanup() will still be called.
    final public function Cancel()
    {
        EventPump::Pump()->Cancel($this);
    }

    // Getters and setters.
    // --------------------------------------------------------------------------
    public function input() { return $this->input; }

    // Marks the event as cancelled. Do not overload this, but rather perform
    // cleanup in end().
    final public function set_cancelled() { $this->cancelled = TRUE; }
    final public function is_cancelled() { return $this->cancelled; }
}
