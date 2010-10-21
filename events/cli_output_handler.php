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

// This implementation of OutputHandler prints messages to the console based on
// the |message| key of an event's data. It will print any messages generated
// by any events. It can also optionally set an exit code to terminate with.
class CLIOutputHandler extends OutputHandler
{
    protected function _DoStart()
    {
        $code = 0;
        // Events are processed in order, newest to oldest.
        foreach (EventPump::Pump()->GetEventChain() as $event) {
            $data = $this->GetEventData($event);
            if ($data->HasKey('message')) {
                print $data->message . "\n";
            }
            $code_keys = array('code', 'status', 'error', 'exit_code');
            foreach ($code_keys as $key) {
                if ($data->HasKey($key) && $data->$key) {
                    $code = $data->$key;
                }
            }
        }
        if ($code) {
            // Unless a special exit code has been set, the call to StopPump()
            // will end execution.
            exit($code);
        }
    }
}
