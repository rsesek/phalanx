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

namespace phalanx\input;

// This class is used to generate unique form keys, so that POST requests
// cannot be forged easily. This uses a delegate for cross-session storage of
// these keys. Upon key creation and deletion, the delegate methods will be
// called to save and remove data.
//
// The form key is defined as an object with the following required properties.
// Implementations are free to store additional data in a form key, so long as
// all required fields are persisted.
// 	FormKey (stdClass) {
//			key (string): Unique form key identifier
// 		timestamp (integer): UNIX timestamp of key creation
// 	}
//
class FormKeyManager
{
	// See FormKeyManagerDelegate (below).
	protected $delegate = null;
	
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
	public function generate()
	{
		$key = sha1(rand() . microtime() . rand());
		
		$form_key = new \stdClass();
		$form_key->key = $key;
		$form_key->timestamp = time();
		$this->delegate->saveFormKey($form_key);
		
		return $key;
	}
	
	// This generates a form key as a hidden <input> element with a given
	// name and ID. If |$name| is not given, |phalanx_form_key| is used.
	public function generateHTML($name = null)
	{
		$name = ($name ?: 'phalanx_form_key');
		return '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $this->generate() . '" />';
	}
	
	// This checks to see if a given key is valid. If so, it will then remove
	// the key from storage. This returns a BOOl, which is if the key was valid.
	public function validate($key)
	{
		$valid = $this->isValid($key);
		$this->invalidate($key);
		return $valid;
	}
	
	// This checks if a given form key is valid and returns a BOOL result.
	public function isValid($key)
	{
		$form_key = $this->delegate->getFormKey($key);
		if (!$form_key)
			return false;
		
		if ($form_key->timestamp < time()-$this->lifetime)
			return false;
		
		return true;
	}
	
	// This marks a key as invalid and removes it from storage.
	public function invalidate($key)
	{
		$this->delegate->deleteKey($key);
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
	public function getFormKey($key);
	public function saveFormKey(\stdClass $form_key);
	public function deleteKey($key);
}

// If you want form validation to happen automatically on every POST request,
// you can raise this event in a global initializer. It will look for any POST
// requests and will validate the form key. If one is not present, it will
// throw an exception.
class ValidateFormKeyEvent extends \phalanx\events\Event
{
	public function __construct(FormKeyManager $manager, $post_variable)
	{
	}
	
	public function init()
	{
		// If we're not in a POST, then simply cancel the event.
		if (count($this->context()->gpc()->get('p')) < 1)
			$this->cancel();
	}
	
	public function handle()
	{
		$key = $this->context()->gpc()->get($this->post_variable);
		if (!$this->manager->validate($key))
		{
			throw new FormKeyException('Form key "' . $key . '" did not validate.');
		}
	}
}

class FormKeyException extends \Exception
{
}
