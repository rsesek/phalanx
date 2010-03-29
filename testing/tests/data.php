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
use \phalanx\data as data;

require_once 'PHPUnit/Framework.php';

// Common includes.
require_once PHALANX_ROOT . '/data/cleaner.php';
require_once PHALANX_ROOT . '/data/form_key.php';
require_once PHALANX_ROOT . '/data/keyed_cleaner.php';
require_once PHALANX_ROOT . '/data/model.php';

class TestFormKeyManagerDelegate implements data\FormKeyManagerDelegate //,
{
    public $did_get = FALSE;
    public $did_save = FALSE;
    public $did_delete = FALSE;

    public $key_storage = array();

    public function GetFormKey($key)
    {
        $this->did_get = TRUE;
        if (isset($this->key_storage[$key]))
            return $this->key_storage[$key];
        return NULL;
    }

    public function SaveFormKey(\phalanx\base\PropertyBag $form_key)
    {
        $this->did_save = TRUE;
        $this->key_storage[$form_key->key] = $form_key;
    }

    public function DeleteKey($key)
    {
        $this->did_delete = TRUE;
        unset($this->key_storage[$key]);
    }
}
