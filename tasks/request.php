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

require_once PHALANX_ROOT . '/base/strict_object.php';

// A Request holds the data in a context-neutral container. This object is
// passed through the Dispatcher until a Router can vend a Task to process the
// input data.
class Request extends \phalanx\base\StrictObject
{
    // The InputFilter that generated the request.
    public $input_filter = NULL;

    // Action string.
    public $action = '';

    // \phalanx\base\Dictionary of parameters.
    public $data = NULL;

    // The Response object for this Request. Will be NULL until the Request is
    // dispatched.
    public $response = NULL;

    public function __construct(InputFilter $input_filter)
    {
          $this->input_filter = $input_filter;
    }
}
