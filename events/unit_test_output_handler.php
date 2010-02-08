<?php
// Phalanx
// Copyright (c) 2010 Blue Static
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
require_once PHALANX_ROOT . '/events/output_handler.php';

// This OutputHandler implementation can be used by application developers to
// test their own events. This OutputHandler does nothing but collect the
// output from ALL events in the chain and stores it.
class UnitTestOutputHandler extends OutputHandler
{
    // The function that transforms an event name into a template name.
    protected $event_data = array();

    protected function _DoStart()
    {
        $event_chain = EventPump::Pump()->GetEventChain();
        foreach ($event_chain as $event)
            $this->event_data[] = $this->GetEventData($event);
    }

    // Returns an array of all event data in the same order of events as the
    // EventPump's event chain. The values are base\PropertyBags.
    public function event_data() { return $this->event_data; }
}
