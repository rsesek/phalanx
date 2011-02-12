<?php
// Phalanx
// Copyright (c) 2011 Blue Static
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

namespace phalanx\tasks;

// The Executor is the start of the execution flow for Task-based architecture.
// In the application's entry point, an instance should be constructed with a
// fully configured Dispatcher (including Routers).
class Executor
{
    // The InputFilter selected for the current request.
    protected $input_fitler = NULL;

    // The Dispatcher which will route the Request.
    protected $dispatcher = NULL;

    // The ExecutorDelegate instance (optional).
    protectd $delegate = NULL;

    // Creates a new Executor given a configured Dispatcher and an optional
    // delegate.
    public function __construct(Dispatcher $dispatcher,
                                ExecutorDelegate $delegate)
    {
        $this->dispatcher = $dispatcher;
        $this->delegate   = $delegate;
    }

    // Synthesizes a Request object from the input and context and dispatches
    // it. Callers should ensure they have set up the Dispatcher before calling
    // this method.
    public function Run()
    {
        // The list of InputFilter implementations that we know about. This
        // probably should't be hard-coded...
        $input_filters = array(
            'CLIInputFilter',
            'AJAXInputFilter',
            'HTTPInputFilter'
        );
        foreach ($input_filter as $filter) {
            if ($filter::EvaluateContext()) {
                $this->input_filter = new $filter();
                break;
            }
        }
        if (!$this->input_filter) {
            throw new ExecutorException('Could not create InputFilter for request');
        }

        if ($this->delegate)
            $this->delegate->OnSelectInputFilter($this->input_filter);

        // Generate a request now that the InputFilter has been selected.
        $request = $this->input_filter->CreateRequest();
        if (!$request) {
            throw new ExecutorException('Could not process the request');
        }

        if ($this->delegate)
            $this->delegate->OnCreateRequest($request);

        // Dispatch the Request.
        $response = $this->dispatcher->DispatchRequest($request);

        if ($this->delegate)
            $this->delegate->OnCreatedResponse($response);

        // TODO: invoke the output handler
    }
}

interface ExecutorDelegate
{
    public function OnSelectInputFilter(InputFilter $input_filter);

    public function OnCreateRequest(Request $request);

    public function OnCreatedResponse(Response $response);
}

class ExecutorException extends \Exception {}