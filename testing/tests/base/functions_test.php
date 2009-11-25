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

namespace phalanx\test;
use \phalanx\base as base;

require_once 'PHPUnit/Framework.php';

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayStripEmpty()
    {
        $array = array(1, 4, 6);
        base\ArrayStripEmpty($array);
        $this->assertEquals(3, count($array));

        $array = array(1, 0, 5, '');
        base\ArrayStripEmpty($array);
        $this->assertEquals(2, count($array));

        $array = array('', 'test' => array('', 6));
        base\ArrayStripEmpty($array);
        $this->assertEquals(1, count($array));
        $this->assertEquals(1, count($array['test']));

        $array = array('foo', NULL, 'bar');
        base\ArrayStripEmpty($array);
        $this->assertEquals(2, count($array));
    }

    public function testUnderscoreToCammelCase()
    {
        $str = 'under_score';
        $this->assertEquals('UnderScore', base\UnderscoreToCammelCase($str));
        $this->assertEquals('underScore', base\UnderscoreToCammelCase($str, FALSE));

        $str = 'many_many_under_scores';
        $this->assertEquals('ManyManyUnderScores', base\UnderscoreToCammelCase($str));
    }
}
