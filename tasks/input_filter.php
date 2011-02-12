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

require_once PHALANX_ROOT . '/tasks/request.php';

// An InputFilter is responsible for taking an environment/request context and
// conforming the input in that environment into a Request object. That object
// is a context-neutral representation of the input parameters of a request, be
// it a command line invocation, a REST web service, or a HTTP front end.
interface InputFilter
{
    // Returns a \phalanx\tasks\Request for the given input context.
    public function CreateRequest();
}
