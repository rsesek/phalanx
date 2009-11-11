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

// The OutputHandler is invoked by the EventPump once all events have been
// processed. The job of this class is to take the fired events, extract their
// output, 
abstract class OutputHandler
{
    // Called by the EventPump when all events have finished processing.
    public function Start()
    {
        $this->_DoStart();
    }

    // Subclasses should implement this method to perform their actual output
    // handling. The EventPump will call Start(), which sets up the object's
    // state before calling _DoStart().
    abstract protected function _DoStart();

    // Returns a PropertyBag of data from |$event| based on its output list.
    protected function _GetEventData(Event $event)
    {
        $data        = new \phalanx\base\PropertyBag();
        $output_list = $event::OutputList();
        foreach ($output_list as $key)
        {
            if (property_exists($event, $key))
                $data->Set($key, $event->$key);
            else if (method_exists($event, $key))
                $data->Set($key, $event->$key());
        }
        return $data;
    }
}
