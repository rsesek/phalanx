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

namespace phalanx\views;

class View
{
    // Base path for loading the template file. Use %s to indicate where the
    // name (passed to the constructor) should be substituted.
    static protected $template_path = '%s.tpl';

    // The cache path for templates. Unlike |$template_path|, this should only
    // be a path, to which the cached template name will be appended. This
    // should not end with a trailing slash.
    static protected $cache_path = '/tmp/phalanx_views';

    // The name of the template.
    protected $template_name = '';

    // Variables to provide to the template.
    protected $vars = NULL;

    // Creates a new template and 
    public function __construct($name)
    {
        $this->template_name = $name;
        $this->vars          = new \phalanx\base\PropertyBag();
        $this->_Cache();
    }

    // Overload property accessors to set view variables.
    public function __get($key)
    {
        return $this->vars->Get($key);
    }
    public function __set($key, $value)
    {
        $this->vars->Set($key, $value);
    }

    // This includes the template and renders it out.
    public function Render()
    {
        $view = &$this->vars;
        include $this->_CachePath($this->template_name);
    }

    // Loads the template from the file system, pre-processes the template, and
    // stores the cached result in the file system.
    protected function _Cache()
    {
        $cache_path = $this->_CachePath($this->template_name);
        if (!file_exists($cache_path))
        {
            $path = sprintf(self::$template_path, $this->template_name);
            $data = file_get_contents($path);

            $data = $this->_ProcessTemplate($data);

            // Cache the file.
            if (!file_put_contents($cache_path, $data))
                throw new ViewException('Could not cache ' . $this->template_name . ' to ' . $cache_path);
        }
    }

    // Returns the cache path for a given template name.
    protected function _CachePath($name)
    {
        return self::$cache_path . '/' . $name . '.phpi';
    }

    // Does any pre-processing on the template.
    protected function _ProcessTemplate($data)
    {
        return $data;
    }

    // Getters and setters.
    // ------------------------------------------------------------------------
    static public function set_template_path($path) { self::$template_path = $path; }
    static function template_path() { return self::$template_path; }

    static public function set_cache_path($path) { self::$cache_path = $path; }
    static public function cache_path() { return self::$cache_path; }

    // Gets the name of the template.
    public function template_name() { return $this->template_name; }

    // Returns the PropertyBag of variables.
    public function vars() { return $this->vars; }
}

class ViewException extends \Exception
{}
