<?php
// Phalanx
// Copyright (c) 2011 Blue Static
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

// A Router is responsible for evaluating a Request in an input context and
// synthesizing tasks. Routers are registered with the Dispatcher, which takes
// the task from the Routers and queues it on the TaskPump.
interface Router
{
    // Evaluates the request input and returns a Task object for the input.
    // If the Router cannot produce a Task for the request, it returns NULL.
    public function VendTask(Request $input);
}

class RouterException extends \Exception
{}

// This following is the set of interfaces that a Router can conform to. There
// is one interface to match every InputFilter class.

// Used for standard HTTP requests.
interface HTTPRouter extends Router
{}

// Used to route AJAX or and REST web service responses.
interface AJAXRouter extends Router
{}

// Used to route CLI interfaces.
interface CLIRouter extends Router
{}
