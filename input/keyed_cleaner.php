<?php
// Phalanx
// Copyright (c) 2009-2010 Blue Static
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

namespace phalanx\input;

require_once PHALANX_ROOT . '/base/key_descender.php';
require_once PHALANX_ROOT . '/input/cleaner.php';

// A KeyedCleaner manages a reference to a base\KeyedDescender. When clean
// operations are performed on an instance of this class, the cleaning operation
// happens onthey specified key of the array. Example:
//
//     $cleaner = new KeyedCleaner($context->gpc);
//     $cleaner->GetHTML('p.some_key');
//     print $context->gpc['p']['some_key'];
class KeyedCleaner
{
    // A ref to the array this cleaner manages. Operations performed on this
    // object will affect this array.
    protected $keyer;

    // Creates a new cleaner, setting a ref to the array.
    public function __construct(/* Array|Object */ & $array)
    {
        $this->keyer = new \phalanx\base\KeyDescender($array);
    }

    public function GetString($key)
    {
        $val = Cleaner::String($this->keyer->Get($key));
        $this->keyer->Set($key, $val);
        return $val;
    }

    public function GetTrimmedString($key)
    {
        $val = Cleaner::TrimmedString($this->keyer->Get($key));
        $this->keyer->Set($key, $val);
        return $val;
    }

    public function GetHTML($key)
    {
        $val = Cleaner::HTML($this->keyer->Get($key));
        $this->keyer->Set($key, $val);
        return $val;
    }

    public function GetInt($key)
    {
        $val = Cleaner::Int($this->keyer->Get($key));
        $this->keyer->Set($key, $val);
        return $val;
    }

    public function GetFloat($key)
    {
        $val = Cleaner::Float($this->keyer->Get($key));
        $this->keyer->Set($key, $val);
        return $val;
    }

    public function GetBool($key)
    {
        $val = Cleaner::Bool($this->keyer->Get($key));
        $this->keyer->Set($key, $val);
        return $val;
    }

    // Getters and setters.
    // -------------------------------------------------------------------------
    public function keyer() { return $this->keyer; }
    public function & ref() { return $this->keyer->root(); }
}
