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
	// The Context in which the event is being handled.
	protected $context;
	
	// The arguments passed to the event upon creation.
	protected $arguments = null;
	
	// Whether or not the event is cancelled.
	protected $cancelled = false;
	
	public function __construct(\phalanx\base\PropertyBag $arguments = null)
	{
		$this->arguments = $arguments;
	}
	
	// Does precondition checks and returns a bool indicating if the event can be
	// handled in the given |context|.
	public static function canRunInContext(Context $context) { return true; }
	
	// Performs setup tasks for event handling. |$this->context| is present at
	// this time. This is a good place to do permission checks.
	public function init() {}
	
	// The actual event handling code. All output is buffered.
	abstract public function handle();
	
	// Events perform clean up tasks here. If |is_cancelled()| is true, then the
	// event handle()ing code was interuppted either internally (the event raised
	// an event) or was prevented from handle()ing due to precondition failures.
	public function end() {}
	
	// Getters and setters.
	// --------------------------------------------------------------------------
	public function arguments() { return $this->arguments; }
	
	public function set_context(Context $context) { $this->context = $context; }
	public function context() { return $this->context; }
	
	// Marks the event as cancelled. Do not overload this, but rather perform
	// cleanup in end().
	public function cancel() { $this->cancelled = true; }
	public function is_cancelled() { return $this->cancelled; }
}
