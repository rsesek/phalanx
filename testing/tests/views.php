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
use \phalanx\input as input;

require_once 'PHPUnit/Framework.php';

// Common includes.
require PHALANX_ROOT . '/views/view.php';

class ViewSuite
{
    static public function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite('Views');

        $suite->addTestFile(TEST_ROOT . '/tests/views/view_test.php');

        return $suite;
    }
}
