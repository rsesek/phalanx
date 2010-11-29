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

namespace phalanx\test;

// This TestListener is meant to print to stdout and show CLI output for the
// running test suite. It unfortunately conflicts with the standard text runner
// UI, so it must be configured manually (see runner.php for an example).
//
// The format of the output is designed to mimic the Google Test (GTest)
// <http://googletest.googlecode.com> framework output.
class TestListener implements \PHPUnit_Framework_TestListener
{
    const COLOR_NONE = 0;
    const COLOR_RED = 1;
    const COLOR_GREEN = 2;

    // The start time of the test suite.
    private $suite_start_time = 0;

    // The suite depth.
    private $suite_depth = 0;

    // The number of errors that occured in a suite.
    private $suite_error_counts = 0;

    // Array of failing tests.
    private $failing = array();

    // An error occurred.
    public function addError(\PHPUnit_Framework_Test $test,
                             \Exception $e,
                             $time)
    {
        $this->_Print(NULL, $this->_ErrorLocation($e));
        $this->_Print('  ', $e->GetMessage());
        $this->_Print('[    ERROR ]', $test->ToString() . ' (' . $this->_Round($time) . ' ms)', self::COLOR_RED);
        ++$this->suite_error_count;
        $this->failing[] = $test->ToString();
    }

    // A failure occurred.
    public function addFailure(\PHPUnit_Framework_Test $test,
                               \PHPUnit_Framework_AssertionFailedError $e,
                               $time)
    {
        $this->_Print(NULL, $this->_ErrorLocation($e));
        $this->_Print('  ', $e->GetMessage());
        $this->_Print('[  FAILED  ]', $test->ToString() . ' (' . $this->_Round($time) . ' ms)', self::COLOR_RED);
        ++$this->suite_error_count;
        $this->failing[] = $test->ToString();
    }

    // Incomplete test.
    public function addIncompleteTest(\PHPUnit_Framework_Test $test,
                                      \Exception $e, $time)
    {
        $this->_Print('INCOMPLETE', $test->ToString());
    }

    // Skipped test.
    public function addSkippedTest(\PHPUnit_Framework_Test $test,
                                   \Exception $e,
                                   $time)
    {
        $this->_Print('SKIPPED', $test->ToString());
    }

    // A test suite started.
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->_Print($this->_SuiteMarker(), $this->_DescribeSuite($suite), self::COLOR_GREEN);
        $this->suite_start_time = microtime(TRUE);
        ++$this->suite_depth;
        $this->suite_error_count = 0;
    }

    // A test suite ended.
    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $main_suite = (--$this->suite_depth == 0);
        $color_red = (($main_suite && count($this->failing)) || $this->suite_error_count > 0);

        $delta = microtime(TRUE) - $this->suite_start_time;
        $this->_Print(
            $this->_SuiteMarker(),
            $this->_DescribeSuite($suite) . ' (' . $this->_Round($delta) . ' ms total)',
            ($color_red ? self::COLOR_RED : self::COLOR_GREEN));
        echo "\n";

        // If this is the main suite (the one to which all other tests/suites
        // are attached), then print the test summary.
        if ($main_suite && $color_red) {
            $count = count($this->failing);
            $plural = ($count > 1 ? 'S' : '');
            echo "  YOU HAVE $count FAILING TEST{$plural}:\n";
            foreach ($this->failing as $test) {
                echo "  $test\n";
            }
            echo "\n";
        }
    }

    // A test started.
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $this->_Print('[ RUN      ]', $test->ToString(), self::COLOR_GREEN);
    }

    // A test ended.
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        $this->_Print('[       OK ]', $test->ToString() . ' (' . $this->_Round($time) . ' ms)', self::COLOR_GREEN);
    }

    // Returns the description for a test suite.
    private function _DescribeSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $count = $suite->Count();
        return sprintf('%d test%s from %s', $count, ($count > 1 ? 's' : ''), $suite->GetName());
    }

    // Returns the test suite marker.
    private function _SuiteMarker()
    {
        if ($this->suite_depth == 0)
            return '[==========]';
        else
            return '[----------]';
    }

    // Prints a line to output.
    private function _Print($column, $annotation, $color = self::COLOR_NONE)
    {
        $color_code = '';
        switch ($color) {
            case self::COLOR_RED: $color_code = '0;31'; break;
            case self::COLOR_GREEN: $color_code = '0;32'; break;
        }
        if ($color != self::COLOR_NONE) {
            $column = "\x1b[{$color_code}m{$column}\x1b[0m";
        }
        echo "$column $annotation\n";
    }

    // Takes in a float from microtime() and returns it formatted to display as
    // milliseconds.
    private function _Round($time)
    {
        return round($time * 1000);
    }

    // Returns the error location as a string.
    private function _ErrorLocation(\Exception $e)
    {
        $trace = $e->GetTrace();
        $frame = NULL;
        // Find the first frame from non-PHPUnit code, which is where the error
        // should have occurred.
        foreach ($trace as $f) {
            if (strpos($f['file'], 'PHPUnit/Framework') === FALSE) {
                $frame = $f;
                break;
            }
        }
        if (!$frame)
            $frame = $trace[0];
        return $frame['file'] . ':' . $frame['line'];
    }
}
