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

require_once 'PHPUnit/Framework.php';

define('PHALANX_ROOT', dirname(dirname(__FILE__)));
define('TEST_ROOT', dirname(__FILE__));

\PHPUnit_Util_Filter::addDirectoryToFilter(TEST_ROOT);

class AllTests
{
    static public function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite('Phalanx');

        require TEST_ROOT . '/tests/base.php';
        $suite->addTestSuite(BaseSuite::suite());

        require TEST_ROOT . '/tests/events.php';
        $suite->addTestSuite(EventsSuite::suite());

        require TEST_ROOT . '/tests/input.php';
        $suite->addTestSuite(InputSuite::suite());

        require TEST_ROOT . '/tests/views.php';
        $suite->addTestSuite(ViewSuite::suite());

        return $suite;
    }
}
