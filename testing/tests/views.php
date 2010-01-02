<?php
// Phalanx
// Copyright (c) 2009-2010 Blue Static
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
use \phalanx\views as views;

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

// Exposer.
class TestView extends views\View
{
    static public function SetupPaths()
    {
        self::set_cache_path(TEST_ROOT . '/tests/views/data/cache/');
        self::set_template_path(TEST_ROOT . '/tests/views/data/%s.tpl');
    }

    public function T_Cache()
    {
        return $this->_Cache();
    }

    public function T_CachePath($name)
    {
        return $this->_CachePath($name);
    }

    public function T_ProcessTemplate($data)
    {
        return $this->_ProcessTemplate($data);
    }
}
