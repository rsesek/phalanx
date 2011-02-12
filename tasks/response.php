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

// A Response holds data processed by Task objects. When a Request is first
// dispatched to a Router, a Response object is created. This response is
// passed to every Task that runs through the pump. Tasks add data members
// to the Response. When all the Tasks have been processed, the final Response
// is transfered to the OutputHandler, which constructs a context-appropriate
// output.
class Response
{
    // The Request that corresponds to this response.
    public $request = NULL;

    // \phalanx\base\Dictionary of data for output.
    public $data = NULL;

    public function __construct(Request $request)
    {
          $this->request = $request;
          $this->data    = new \phalanx\base\Dictionary();
    }
}
