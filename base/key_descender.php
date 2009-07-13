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

// This manages a ref to a "descendable" type. A descendable type is either an
// object or an array, which are key-value structures that can hold,
// recursively, other descendable types. A KeyDescender allows a caller to
// access keys and values in a type-agnostic manner.
class KeyDescender
{
	// The root ref we descend.
	protected $root;
	
	// Whether or not we throw exceptions for undefined keys. This enables
	// strict keyed access. Otherwise, NULL will be returned on failure.
	protected $throw_undefined_errors = true;
	
	public function __construct(/* Array|Object */ & $root)
	{
		if (!self::isDescendable($root))
			throw new KeyDescenderException('Cannot create a KeyDescender on a non-descendable reference');
		$this->root = &$root;
	}
	
	// Checks whether a given type is descendable. This is used to create the
	// base case for recursive descention.
	public static function isDescendable($value)
	{
		return (is_array($value) || is_object($value));
	}
	
	// Returns a ref to the keyed value. Example: "foo.bar.baz"
	public function & get($key)
	{
		$stack = explode('.', $key);
		$current = &$this->root;
		for ($i = 0; $i < sizeof($stack); $i++)
		{
			try
			{
				$current = $this->_get($current, $stack[$i]);
			}
			// Catch subkey exceptions and re-throw them as main key ones.
			catch (UndefinedKeyException $e)
			{
				if ($this->throw_undefined_errors)
					throw new UndefinedKeyException("Undefined key $key");
				return null;
			}
		}
		
		return $current;
	}
	
	// Returns a value from a given key in a descendable.
	protected function & _get($descendable, $single_key)
	{
		if (is_array($descendable))
		{
			if (isset($descendable[$single_key]))
				return $descendable[$single_key];
			else
				throw new UndefinedKeyException("Undefined key '$key' on $descendable");
		}
		else if (is_object($descendable))
		{
			if (isset($descendable->$single_key))
				return $descendable->$single_key;
			else
				throw new UndefinedKeyException("Undefined '$key' on $descendable");
		}
		else
		{
			throw new KeyDescenderException("'$descendable' is not descendable");
		}
	}
	
	// Getters and setters.
	// -------------------------------------------------------------------------
	public function & root() { return $this->root; }
	
	public function throw_undefined_errors() { return $this->throw_undefined_errors; }
	public function set_throw_undefined_errors($throw)
	{
		$this->throw_undefined_errors = $throw;
	}
}

class KeyDescenderException extends \Exception
{
}

class UndefinedKeyException extends KeyDescenderException
{
}
