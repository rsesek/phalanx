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

namespace phalanx\base;

require_once PHALANX_ROOT . '/base/key_descender.php';

// A dictinoary can be used instead of an array to store key-value pairs in
// an object. While \stdClass can be used for this, too, Dictionary provides
// additional methods to make working with it slightly easier.
//
// Dictionary is also a KeyDescender, except when it is created with an
// object, the contents are copied rather than referenced.
class Dictionary extends KeyDescender
{
    // A Dictionary can either be created from a descendable type, or from
    // a varargs of key-value pairs. If the varags constructor is used,
    // the form is |new Dictionary(key1, val1, key2, val2)|.
    public function __construct($properties = array())
    {
        $args = func_get_args();
        if (count($args) == 1 && self::IsDescendable($args[0])) {
            $this->root = $args[0];
        } else {
            $this->root = array();
            for ($i = 0; $i < count($args); $i += 2) {
                $this->root[$args[$i]] = $args[$i + 1];
            }
        }
    }

    // Sets a key-value pair.
    public function __set($key, $value)
    {
        parent::__set($key, $value);
    }

    // Returns the value for a given key.
    public function __get($key)
    {
        return $this->GetSilent($key);
    }

    // Returns the number of items in the Dictionary.
    public function Count()
    {
        return count($this->root);
    }

    // Returns an array containing all the keys in the property bag.
    public function AllKeys()
    {
        return array_keys($this->root);
    }

    // Returns an array of just the values in the property bag.
    public function AllValues()
    {
        return array_values($this->root);
    }

    // Returns the entire property bag as an associative array/hash.
    public function ToArray()
    {
        return $this->root;
    }

    // Checks whether or not a given key has been set in the property bag.
    public function HasKey($key)
    {
        return isset($this->root[$key]);
    }

    // Checks if a value is in the property bag.
    public function Contains($value)
    {
        return in_array($value, $this->root);
    }
}
