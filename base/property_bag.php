<?php
// Phalanx
// Copyright (c) 2009 Blue Static
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

// A property bag can be used instead of an array to store key-value pairs in
// an object. While \stdClass can be used for this, too, PropertyBag provides
// additional methods to make working with it slightly easier.
//
// PropertyBag is also a KeyDescender, except when it is created with an
// object, the contents are copied rather than referenced.
class PropertyBag extends KeyDescender
{
	// We override __set() and __get() and put the data in here.
	protected $properties = array();
	
	public function __construct($properties = array())
	{
		if (self::isDescendable($properties))
			$this->properties = $properties;
		$this->root = &$this->properties;
	}
	
	// Sets a key-value pair.
	public function __set($key, $value)
	{
		parent::__set($key, $value);
	}
	
	// Returns the value for a given key.
	public function __get($key)
	{
		return $this->getSilent($key);
	}
	
	// Returns an array containing all the keys in the property bag.
	public function allKeys()
	{
		return array_keys($this->properties);
	}
	
	// Returns an array of just the values in the property bag.
	public function allValues()
	{
		return array_values($this->properties);
	}
	
	// Returns the entire property bag as an associative array/hash.
	public function toArray()
	{
		return $this->properties;
	}
	
	// Checks whether or not a given key has been set in the property bag.
	public function hasKey($key)
	{
		return isset($this->properties[$key]);
	}
	
	// Checks if a value is in the property bag.
	public function contains($value)
	{
		return in_array($value, $this->properties);
	}
}
