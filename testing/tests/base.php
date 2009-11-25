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

// Common includes.
require PHALANX_ROOT . '/base/functions.php';
require PHALANX_ROOT . '/base/key_descender.php';
require PHALANX_ROOT . '/base/property_bag.php';

class BaseSuite
{
    static public function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite('Base');

        $suite->addTestFile(TEST_ROOT . '/tests/base/functions_test.php');
        $suite->addTestFile(TEST_ROOT . '/tests/base/key_descender_test.php');
        $suite->addTestFile(TEST_ROOT . '/tests/base/property_bag_test.php');

        return $suite;
    }
}
