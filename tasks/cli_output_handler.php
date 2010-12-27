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

namespace phalanx\tasks;

require_once PHALANX_ROOT . '/tasks/task_pump.php';
require_once PHALANX_ROOT . '/tasks/output_handler.php';

// This implementation of OutputHandler prints messages to the console based on
// the |message| key of a task's data. It will print any messages generated
// by any tasks. It can also optionally set an exit code to terminate with.
class CLIOutputHandler extends OutputHandler
{
    public function Start()
    {
        $code = 0;
        // Tasks are processed in order, newest to oldest.
        foreach (TaskPump::Pump()->GetTaskHistory() as $task) {
            $data = $this->GetTaskData($task);
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
