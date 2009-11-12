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

namespace phalanx\events;

class HTTPDispatcher extends Dispatcher
{
    // The name of the input key to get the event name from.
    protected $event_input_key;

    // The request method, uppercase.
    protected $request_method;

    // The input parsed from the URL.
    protected $url_input;

    // Create a new HTTPDispatcher that will synthesize events based on the
    // event name specified in the HTTP input variable, keyed by
    // |$event_input_key|.
    public function __construct($event_input_key = 'phalanx_event')
    {
        $this->event_input_key = $event_input_key;
    }

    // Override Start() in order to parse the URL.
    public function Start()
    {
        $this->request_method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->url_input      = $this->_TokenizeURL($_GET['__dispatch__']);
        parent::Start();
    }

    // Getters and setters.
    // ------------------------------------------------------------------------
    public function event_input_key() { return $this->event_input_key; }

    // This splits a request URL into the event name and then appropriate key
	// value matching. URLs can take the form:
	//   /event_name/id
	//   /event_name/k1/v1/k2/v2/
	protected function _TokenizeURL($url)
	{
        $input = new \phalanx\base\PropertyBag();
		$parts = explode('/', trim($url, '/'));

        $input->Set('_event', $parts[0]);
        array_shift($parts);

        if (count($parts) == 1)
        {
            $input->Set('_id', $parts[0]);
            return $input;
        }
   
		for ($i = 0; $i < count($parts); $i += 2)
		{
			if (!isset($parts[$i]) || !isset($parts[$i+1]))
				throw new HTTPDispatcherException("Invalid key-value pair in URL '$url'");
			$input->Set($parts[$i], $parts[$i+1]);
		}
		return $input;
	}

    // Gets the event name.
    protected function _GetEventName()
    {
        $url_event = $this->url_input->Get('_event');
        if ($url_event != NULL)
            return $url_event;
        if ($this->request_method == 'POST')
            return $_POST[$this->event_input_key];
        throw new HTTPDispatcherException('Cannot determine event name in ' . __METHOD__);
    }

    // Returns the input based on the keys provided.
    protected function _GetInput(Array $keys)
    {
        $input = new \phalanx\base\PropertyBag();
        if ($this->request_method == 'GET')
        {
            foreach ($keys as $key)
                if ($this->url_input->HasKey($key))
                    $input->Set($key, $this->url_input->Get($key));
            return $input;
        }
        else if ($this->request_method == 'POST')
        {
            foreach ($keys as $key)
                if (isset($_POST[$key]))
                    $input->Set($key, $_POST[$key]);
            return $input;
        }
        else
        {
            throw new HTTPDispatcherException('Unknown request method "' . $this->request_method . '"');
        }
        return $input;
    }
}

class HTTPDispatcherException extends \Exception
{}
