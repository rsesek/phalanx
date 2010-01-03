<?php
// Phalanx
// Copyright (c) 2009-2010 Blue Static
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

require_once PHALANX_ROOT . '/events/event_pump.php';

// The Dispatcher synthesizes Event objects and puts them into the EventPump.
abstract class Dispatcher
{
    // A lambda that takes an event name and converts it to a fully qualified
    // class name. This is then instantiated.
    protected $event_loader = NULL;

    // The EventPump the Dispatcher will invoke methods on. If this is NULL,
    // the Dispatcher will use EventPump::Pump() singleton.
    protected $pump = NULL;

    // This will begin synthesizing events and sending them to the pump.
    public function Start()
    {
        $event_name  = $this->_GetEventName();
        $loader      = $this->event_loader;
        $event_class = $loader($event_name);
        $input       = $this->_GetInput($event_class::InputList());
        $event       = new $event_class($input);
        $this->pump()->PostEvent($event);
    }

    // Extracts the event name, to be processed via |$event_loader| from the
    // input keys. Returns the event name (not class name) as a string.
    abstract protected function _GetEventName();

    // Called by Start(). This should return a PropertyBag of input that is to
    // be passed to the event. This function should gather input for the keys
    // passed to it.
    abstract protected function _GetInput(Array $keys);

    // Getters and setters.
    public function set_event_loader(\Closure $loader) { $this->event_loader = $loader; }
    public function event_loader() { return $this->event_loader; }

    public function set_pump(EventPump $pump) { $this->pump = $pump; }
    public function pump()
    {
        if (!$this->pump)
            return EventPump::Pump();
        return $this->pump;
    }
}
