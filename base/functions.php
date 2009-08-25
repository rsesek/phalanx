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
use \phalanx\events as events;

// Iterates over an array and unsets any empty elements in the array. This
// operates on the parameter itself.
function array_strip_empty(Array & $array)
{
	foreach ($array as $key => $value)
		if (is_array($array[$key]))
			array_strip_empty($array[$key]);
		else if (empty($value))
			unset($array[$key]);
}

// Turns an under_scored string into a CamelCased one. If |$first_char| is
// true, then the first character will also be capatalized.
function underscore_to_cammelcase($string, $first_char = true)
{
	if ($first_char)
		$string[0] = strtoupper($string[0]);
	return preg_replace_callback('/_([a-z])/', function($c) { return strtoupper($c[1]); }, $string);
}