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
use phalanx\views\View;

require_once 'PHPUnit/Framework.php';

require_once TEST_ROOT . '/tests/views.php';

class ViewTest extends \PHPUnit_Framework_TestCase
{
    public $saved_singleton = array();

    public function setUp()
    {
        $this->saved_singleton['template_path'] = View::template_path();
        $this->saved_singleton['cache_path']    = View::cache_path();
    }

    public function tearDown()
    {
        View::set_template_path($this->saved_singleton['template_path']);
        View::set_cache_path($this->saved_singleton['cache_path']);
    }

    public function testTemplatePath()
    {
        $this->assertEquals('%s.tpl', View::template_path());

        $path = '/webapp/views/%s.tpl';
        View::set_template_path($path);
        $this->assertEquals($path, View::template_path());
    }

    public function testCachePath()
    {
        $this->assertEquals('/tmp/phalanx_views', View::cache_path());

        $path = '/cache/path';
        View::set_cache_path($path);
        $this->assertEquals($path, View::cache_path());
    }

    public function testCtorAndTemplateName()
    {
        $view = $this->getMock('phalanx\views\View', array('_Cache'), array('test_tpl'));
        $view->expects($this->once())->method('_Cache');
        $this->assertEquals('test_tpl', $view->template_name());
    }
}
