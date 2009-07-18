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

namespace phalanx\input;

// A KeyedCleaner manages a reference to a base\KeyedDescender. When clean
// operations are performed on an instance of this class, the cleaning operation
// happens onthey specified key of the array. Example:
//
// 	$cleaner = new KeyedCleaner($context->gpc);
// 	$cleaner->getHTML('p.some_key');
// 	print $context->gpc['p']['some_key'];
class KeyedCleaner
{
	// A ref to the array this cleaner manages. Operations performed on this
	// object will affect this array.
	protected $ref;
	
	// Creates a new cleaner, setting a ref to the array.
	public function __construct(/* Array|Object */ & $array)
	{
		$this->ref = new \phalanx\base\KeyDescender($array);
	}
	
	public function getString($key)
	{
		return Cleaner::string($this->ref->get($key));
	}
	
	public function getTrimmedString($key)
	{
		return Cleaner::trimmed_string($this->ref->get($key));
	}
	
	public function getHTML($key)
	{
		return Cleaner::html($this->ref->get($key));
	}
	
	public function getInt($key)
	{
		return Cleaner::int($this->ref->get($key));
	}
	
	public function getFloat($key)
	{
		return Cleaner::float($this->ref->get($key));
	}
	
	public function getBool($key)
	{
		return Cleaner::bool($this->ref->get($key));
	}
	
	// Getters and setters.
	// -------------------------------------------------------------------------
	public function & ref() { return $this->ref->root(); }
}
