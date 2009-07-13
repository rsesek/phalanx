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

// An ArrayCleaner manages a reference to an array. When clean operations are
// performed on an instance of this class, the cleaning operation happens on
// they specified key of the array. Example:
//
// 	$cleaner = new ArrayCleaner($context->gpc);
// 	$cleaner->cleanHTML('p.some_key');
// 	print $context->gpc['p']['some_key'];
//
// Note that cleaning calls use the base\KeyDescender to access values.
class ArrayCleaner
{
	// A ref to the array this cleaner manages. Operations performed on this
	// object will affect this array.
	protected $array;
	
	// Creates a new cleaner, setting a ref to the array.
	public function __construct(Array& $array)
	{
		$this->array = &$array;
	}
	
	
	
	// Getters and setters.
	// -------------------------------------------------------------------------
	public function & array_ref() { return $this->array; }
}
