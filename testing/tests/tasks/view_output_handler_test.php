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
use \phalanx\tasks as tasks;

require_once TEST_ROOT . '/tests/views.php';

class CustomViewTask extends TestTask implements \phalanx\views\CustomViewTask
{
    public function CustomTemplateName()
    {
        return 'custom_test';
    }
}

class ViewOutputHandlerTest extends \PHPUnit_Framework_TestCase
{
    public $handler;
    public $pump;

    public $tpl_path;
    public $cache_path;

    public function setUp()
    {
        $this->handler = new tasks\ViewOutputHandler();
        $this->pump    = $this->getMock('phalanx\tasks\TaskPump', array('_Exit'));
        tasks\TaskPump::T_set_pump($this->pump);

        $this->tpl_path = TestView::template_path();
        $this->cache_path = TestView::cache_path();
    }

    public function tearDown()
    {
        TestView::set_template_path($this->tpl_path);
        TestView::set_cache_path($this->cache_path);
    }

    public function testSetTemplateLoader()
    {
        $fn = function() { return 42; };
        $this->handler->set_template_loader($fn);
        $this->assertSame($fn, $this->handler->template_loader());
        $set_fn = $this->handler->template_loader();
        $this->assertEquals($fn(), $set_fn());
    }

    public function testDoStart()
    {
        $this->handler->set_template_loader(function() { return 'view_oh_test'; });
        $this->pump->set_output_handler($this->handler);
        $this->pump->QueueTask(new TestTask());
        TestView::SetupPaths();

        ob_start();
        $this->pump->StopPump();
        $data = ob_get_contents();
        ob_end_clean();

        $expected = "ViewOutputHandler test template.\n(1) foo (2) bar";
        $this->assertEquals($expected, $data);
    }

    public function testCustomTemplate()
    {
        $this->handler->set_template_loader(function() { return 'render_test'; });
        $this->pump->set_output_handler($this->handler);
        $this->pump->QueueTask(new CustomViewTask());
        TestView::SetupPaths();

        ob_start();
        $this->pump->StopPump();
        $data = ob_get_contents();
        ob_end_clean();

        $expected = "This is a custom template.";
        $this->assertEquals($expected, $data);
    }
}
