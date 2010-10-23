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

namespace phalanx\data;
use \phalanx\base\PropertyBag as PropertyBag;

require_once PHALANX_ROOT . '/base/property_bag.php';
require_once PHALANX_ROOT . '/data/cleaner.php';

// This class is used to generate unique form keys, so that POST requests
// cannot be forged easily. This uses a delegate for cross-session storage of
// these keys. Upon key creation and deletion, the delegate methods will be
// called to save and remove data.
//
// The form key is defined as an object with the following required properties.
// Implementations are free to store additional data in a form key, so long as
// all required fields are persisted.
//     FormKey (stdClass) {
//                key (string): Unique form key identifier
//         timestamp (integer): UNIX timestamp of key creation
//     }
//
class FormKeyManager
{
    // See FormKeyManagerDelegate (below).
    protected $delegate = NULL;

    // The amount of time, in seconds, for which a form key should be considered
    // valid. Default is 1 hour.
    protected $lifetime = 3600;

    // Initializes a FormKeyManager with a delegate.
    public function __construct(FormKeyManagerDelegate $delegate)
    {
        $this->delegate = $delegate;
    }

    // This generates a new form key (a SHA1 hash) that can be used to uniquely
    // validate a given form POST.
    public function Generate()
    {
        $form_key            = new PropertyBag();
        $form_key->key       = sha1(rand() . microtime() . rand());
        $form_key->timestamp = time();
        $this->delegate->SaveFormKey($form_key);

        return $form_key->key;
    }

    // This generates a form key as a hidden <input> element with a given
    // name and ID. If |$name| is not given, |phalanx_form_key| is used.
    public function GenerateHTML($name = NULL)
    {
        $name = ($name ?: 'phalanx_form_key');
        return '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $this->Generate() . '" />';
    }

    // This checks to see if a given key is valid. If so, it will then remove
    // the key from storage. This returns a BOOl, which is if the key was valid.
    public function Validate($key)
    {
        $valid = $this->IsValid($key);
        $this->Invalidate($key);
        return $valid;
    }

    // This checks if a given form key is valid and returns a BOOL result.
    public function IsValid($key)
    {
        $form_key = $this->delegate->GetFormKey($key);
        if (!$form_key)
            return FALSE;

        if ($form_key->timestamp < (time() - $this->lifetime))
            return FALSE;

        return TRUE;
    }

    // This marks a key as invalid and removes it from storage.
    public function Invalidate($key)
    {
        $this->delegate->DeleteKey($key);
    }

    // Setters and getters.
    // -------------------------------------------------------------------------
    public function set_delegate(FormKeyManagerDelegate $delegate) { $this->delegate = $delegate; }
    public function delegate() { return $this->delegate; }
}

// This interface should be implemented by FormKeyManager's delegate to store
// and retrieve keys. All methods must be implemented.
interface FormKeyManagerDelegate
{
    public function GetFormKey($key);
    public function SaveFormKey(PropertyBag $form_key);
    public function DeleteKey($key);
}

// If you want form validation to happen automatically on every POST request,
// you can post this task in a global initializer. It will look for any POST
// requests and will validate the form key. If one is not present, it will
// throw an exception.
class ValidateFormKeyTask extends \phalanx\tasks\Task
{
    // The form key manager.
    protected $manager;

    public function __construct(FormKeyManager $manager)
    {
        $this->manager = $manager;
    }

    static public function InputList()
    {
        return array('phalanx_form_key');
    }

    static public function OutputList()
    {
        return NULL;
    }

    public function WillFire()
    {
        // If we're not in a POST, then simply cancel the task.
        if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD']) != 'POST')
            $this->Cancel();
    }

    public function Fire()
    {
        $key = Cleaner::HTML($_POST['phalanx_form_key']);
        if (!$key || !$this->manager->Validate($key))
            throw new FormKeyException('Form key "' . $key . '" did not validate.');
    }
}

class FormKeyException extends \Exception
{
}
