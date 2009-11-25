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

class KeyedCleanerTest extends \PHPUnit_Framework_TestCase
{
    public $fixture;
    public $cleaner;

    public function setUp()
    {
        $this->fixture = array(
            'str'        => 'string',
            'tstr'    => ' trimmed string    ',
            'html'    => '<strong>html</strong>',
            'dqstr'    => '"double quoted string"',
            'sqstr'    => "'single quoted string'",
            'dqhtml'    => '<strong>html with "double quotes"</strong>',
            'int'        => 42,
            'float'    => 3.14159,
            'bool1'    => TRUE,
            'bool0'    => FALSE,
            'boolT'    => 'TrRuE',
            'boolF'    => 'FALSE',
            'boolY'    => 'YES',
            'boolN'    => 'no',
            'entity' => 'red, green, & blue'
        );
        $this->cleaner = new input\KeyedCleaner($this->fixture);
    }

    public function testConstructWithRef()
    {
        $test = $this->cleaner->ref();
        $this->assertEquals('string', $test['str']);
    }

    public function testGetKeyer()
    {
        $this->assertEquals('phalanx\base\KeyDescender', get_class($this->cleaner->keyer()));
    }

    public function testCleanString()
    {
        $this->cleaner->GetString('str');
        $this->assertEquals('string', $this->fixture['str']);

        $this->cleaner->GetString('tstr');
        $this->assertEquals(' trimmed string    ', $this->fixture['tstr']);

        $this->cleaner->GetString('html');
        $this->assertEquals('<strong>html</strong>', $this->fixture['html']);

        $this->cleaner->GetString('dqstr');
        $this->assertEquals('"double quoted string"', $this->fixture['dqstr']);

        $this->cleaner->GetString('sqstr');
        $this->assertEquals("'single quoted string'", $this->fixture['sqstr']);

        $this->cleaner->GetString('dqhtml');
        $this->assertEquals('<strong>html with "double quotes"</strong>', $this->fixture['dqhtml']);

        $this->cleaner->GetString('entity');
        $this->assertEquals('red, green, & blue', $this->fixture['entity']);
    }

    public function testCleanTrimmedStr()
    {
        $this->cleaner->GetTrimmedString('str');
        $this->assertEquals('string', $this->fixture['str']);

        $this->cleaner->GetTrimmedString('tstr');
        $this->assertEquals('trimmed string', $this->fixture['tstr']);
    }

    public function testCleanHTML()
    {
        $this->cleaner->GetHTML('str');
        $this->assertEquals('string', $this->fixture['str']);

        $this->cleaner->GetHTML('html');
        $this->assertEquals('&lt;strong&gt;html&lt;/strong&gt;', $this->fixture['html']);

        $this->cleaner->GetHTML('dqstr');
        $this->assertEquals('&quo;double quoted string&quo;', $this->fixture['dqstr']);

        $this->cleaner->GetHTML('sqstr');
        $this->assertEquals("'single quoted string'", $this->fixture['sqstr']);

        $this->cleaner->GetHTML('dqhtml');
        $this->assertEquals('&lt;strong&gt;html with &quo;double quotes&quo;&lt;/strong&gt;', $this->fixture['dqhtml']);

        $this->cleaner->GetHTML('entity');
        $this->assertEquals('red, green, & blue', $this->fixture['entity']);
    }

    public function testCleanInt()
    {
        $this->cleaner->GetInt('str');
        $this->assertEquals(0, $this->fixture['str']);

        $this->cleaner->GetInt('int');
        $this->assertEquals(42, $this->fixture['int']);

        $this->cleaner->GetInt('float');
        $this->assertEquals(3, $this->fixture['float']);

        $this->cleaner->GetInt('bool1');
        $this->assertEquals(1, $this->fixture['bool1']);
    }

    public function testCleanFloat()
    {
        $this->cleaner->GetFloat('str');
        $this->assertEquals(0.0, $this->fixture['str']);

        $this->cleaner->GetFloat('int');
        $this->assertEquals(42.0, $this->fixture['int']);

        $this->cleaner->GetFloat('float');
        $this->assertEquals(3.14159, $this->fixture['float']);
    }

    public function testCleanBool()
    {
        $this->cleaner->GetBool('bool1');
        $this->assertEquals(TRUE, $this->fixture['bool1']);

        $this->cleaner->GetBool('boolT');
        $this->assertEquals(TRUE, $this->fixture['boolT']);

        $this->cleaner->GetBool('boolY');
        $this->assertEquals(TRUE, $this->fixture['boolY']);

        $this->cleaner->GetBool('bool0');
        $this->assertEquals(FALSE, $this->fixture['bool0']);

        $this->cleaner->GetBool('boolF');
        $this->assertEquals(FALSE, $this->fixture['boolF']);

        $this->cleaner->GetBool('boolN');
        $this->assertEquals(FALSE, $this->fixture['boolN']);
    }
}
