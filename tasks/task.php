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

namespace phalanx\tasks;

// A base representation of an event. Tasks should not generate any output,
// rather they should store data in member variables, which can then be used by
// the view system.
abstract class Task
{
    // The input passed to the event upon creation.
    protected $input = NULL;

    // Whether or not the event is cancelled.
    private $cancelled = FALSE;

    // The state of the event. This should ONLY ever be changed by the pump.
    private $state = 0;

    // Creates an instance of the Task class. The PropertyBag of input is
    // assembled for the Task by the Dispatcher. It collects input variables
    // based on the keys the Task asks for via the InputList() method.
    public function __construct(\phalanx\base\PropertyBag $input = NULL)
    {
        $this->input = $input;
    }

    // Returns an array of input keys the Task requires in order to perform
    // its work. Returning NULL means this Task requires no input.
    abstract static public function InputList();

    // Returns an array of keys that exist on this Task class that the
    // OutputHandler can access. NULL for no output.
    abstract static public function OutputList();

    // Called before the TaskPump is preparing to Fire() the event. This is a
    // good place to put permission and general sanity checks.
    public function WillFire() {}

    // The actual processing work of the Task happens in Fire(). As the Task
    // generates output, it should put it into the properties it declared in
    // OutputList().
    abstract public function Fire();

    // Called after the TaskPump is done with the Task. This will be called
    // even if the Task is preempted by another and this one does not Fire().
    public function Cleanup() {}

    // Cancels the current event. Cleanup() will still be called.
    final public function Cancel()
    {
        TaskPump::Pump()->Cancel($this);
    }

    // Getters and setters.
    // --------------------------------------------------------------------------
    public function input() { return $this->input; }

    // Marks the event as cancelled. Do not overload this, but rather perform
    // cleanup in end().
    final public function set_cancelled() { $this->cancelled = TRUE; }
    final public function is_cancelled() { return $this->cancelled; }

    // Sets and gets the state. Setting the state is reserved for the
    // TaskPump. Changing it outside that context WILL result in unexpected
    // application behavior.
    final public function set_state($state) { $this->state = $state; }
    final public function state() { return $this->state; }
}
