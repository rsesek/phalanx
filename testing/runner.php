<?php
// Phalanx
// Copyright (c) 2010 Blue Static
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

if (!defined('PHALANX_ROOT')) {
    define('PHALANX_ROOT', dirname(dirname(__FILE__)));
    define('TEST_ROOT', dirname(__FILE__));
}

if (extension_loaded('xdebug')) {
    xdebug_disable();
}

ini_set('memory_limit', -1);

// PHPUnit 3.5.5.
require_once 'PHPUnit/Autoload.php';
require_once TEST_ROOT . '/bootstrap.php';
require_once TEST_ROOT . '/test_listener.php';

$collector = new PHPUnit_Runner_IncludePathTestCollector(
    array(TEST_ROOT . '/tests/'),
    array('_test.php')
);
$suite = new PHPUnit_Framework_TestSuite('Phalanx Unit Tests');
$suite->AddTestFiles($collector->CollectTests());

$result = new PHPUnit_Framework_TestResult();
$result->AddListener(new \phalanx\test\TestListener());

$suite->Run($result);

exit;
