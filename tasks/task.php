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

// A base representation of a task. Tasks should not generate any output,
// rather they should store data in member variables, which can then be used by
// the view system.
abstract class Task
{
    // The input passed to the task upon creation.
    protected $input = NULL;

    // Whether or not the task is cancelled.
    private $cancelled = FALSE;

    // Creates an instance of the Task class. The Dictionary of input is
    // assembled for the Task by the Dispatcher. It collects input variables
    // based on the keys the Task asks for via the InputList() method.
    public function __construct(\phalanx\base\Dictionary $input = NULL)
    {
        $this->input = $input;
    }

    // Returns an array of input keys the Task requires in order to perform
    // its work. Returning NULL means this Task requires no input.
    static public function InputList()
    {
        return NULL;
    }

    // Returns an array of keys that exist on this Task class that the
    // OutputHandler can access. NULL for no output.
    static public function OutputList()
    {
        return NULL;
    }

    // The actual processing work of the Task happens in Run(). As the Task
    // generates output, it should put it into the properties it declared in
    // OutputList().
    abstract public function Run();

    // Cancels the current task, which runs the next deferred task or generates
    // output and ends execution.
    final public function Cancel()
    {
        TaskPump::Pump()->Cancel($this);
    }

    // Getters and setters.
    // --------------------------------------------------------------------------
    public function input() { return $this->input; }

    // Marks the task as cancelled. Reserved for use by the TaskPump.
    final public function set_cancelled() { $this->cancelled = TRUE; }
    final public function is_cancelled() { return $this->cancelled; }
}
