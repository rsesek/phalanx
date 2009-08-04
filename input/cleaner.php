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

// This class holds only static methods that perform cleaning operations.

class Cleaner
{
	public static function string($str)
	{
		return "$str";
	}
	
	public static function trimmed_string($str)
	{
		return trim($str);
	}
	
	public static function html($str)
	{
		$find = array(
			'<',
			'>',
			'"'
		);
		$replace = array(
			'&lt;',
			'&gt;',
			'&quo;'
		);
		return str_replace($find, $replace, $str);
	}
	
	public static function int($int)
	{
		return intval($int);
	}
	
	public static function float($float)
	{
		return floatval($float);
	}
	
	public static function bool($bool)
	{
		$str = strtolower(self::trimmed_string($bool));
		if ($str == 'yes' || $str == 'true')
			return true;
		else if ($str == 'no' || $str == 'false')
			return false;
		return (bool)$bool;
	}
}
