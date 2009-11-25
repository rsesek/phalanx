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
    static public function String($str)
    {
        return "$str";
    }

    static public function TrimmedString($str)
    {
        return trim($str);
    }

    static public function HTML($str)
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

    static public function Int($int)
    {
        return intval($int);
    }

    static public function Float($float)
    {
        return floatval($float);
    }

    static public function Bool($bool)
    {
        $str = strtolower(self::TrimmedString($bool));
        if ($str == 'yes' || $str == 'TRUE')
            return TRUE;
        else if ($str == 'no' || $str == 'FALSE')
            return FALSE;
        return (bool)$bool;
    }
}
