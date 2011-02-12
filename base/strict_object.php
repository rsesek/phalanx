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

namespace phalanx\base;

class StrictObject
{
    // Gets |$key| on the object if it is an explicitly listed ivar.
    public function __get($key)
    {
        throw new StrictObjectException('Cannot get ' . get_class($this) . '::' . $key);
    }

    // Sets |$key| to |$var| on the object if it is an explicitly listed ivar.
    public function __set($key, $value)
    {
        throw new StrictObjectException('Cannot set ' . get_class($this) . '::' . $key);
    }
}

class StrictObjectException extends \Exception {}
