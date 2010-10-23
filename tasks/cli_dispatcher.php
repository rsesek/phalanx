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

require_once PHALANX_ROOT . '/base/property_bag.php';
require_once PHALANX_ROOT . '/tasks/dispatcher.php';

// This dispatcher instance creates a command line interface for running tasks.
// You can run the program and the arguments are parsed as such:
//   program.php [task-name] --input_name foobar --key2 value
class CLIDispatcher extends Dispatcher
{
    // The input parsed from the command line arguments.
    protected $cli_input;

    // The raw array of unparsed arguments.
    protected $argv = array();

    public function __construct($argv)
    {
        $this->argv = $argv;
    }

    // Override Start() in order to parse the arguments.
    public function Start()
    {
        $this->cli_input = $this->_ParseArguments($this->argv);
        parent::Start();
    }

    // This splits an argument string of flags into key/value pairs.
    protected function _ParseArguments($args)
    {
        $input = new \phalanx\base\PropertyBag();

        // Remove the program's name.
        array_shift($args);

        // Set the task name.
        $input->Set('_task', $args[0]);
        array_shift($args);

        if (count($args) == 1)
        {
            $input->Set('_id', $args[0]);
            return $input;
        }

        for ($i = 0; $i < count($args); $i += 2)
        {
            if (!isset($args[$i]) || !isset($args[$i+1]))
                throw new CLIDispatcherException("Invalid argument pair: " . $args[$i]);
            if (substr($args[$i], 0, 2) != '--')
                throw new CLIDispatcherException("Argument flag does is invalid: " . $args[$i]);
            $input->Set(substr($args[$i], 2), $args[$i+1]);
        }
        return $input;
    }

    // Gets the task name.
    protected function _GetTaskName()
    {
        return $this->cli_input->Get('_task');
    }

    // Returns the input based on the keys provided.
    protected function _GetInput(Array $keys)
    {
        $input = new \phalanx\base\PropertyBag();
        foreach ($keys as $key)
            if ($this->cli_input->HasKey($key))
                $input->Set($key, $this->cli_input->Get($key));
        return $input;
    }
}

class CLIDispatcherException extends \Exception
{}
