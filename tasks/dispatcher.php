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

require_once PHALANX_ROOT . '/tasks/router.php';
require_once PHALANX_ROOT . '/tasks/task_pump.php';

// The Dispatcher has various Routers attached in a priority queue. When the
// Dispatcher is started, it will go down the Router chain until one of
// the Routers vends a Task object that it can queue on the pump.
class Dispatcher
{
    // The TaskPump the Dispatcher will invoke methods on. If this is NULL,
    // the Dispatcher will use TaskPump::Pump() singleton.
    protected $pump = NULL;

    // The priority queue of Router objects.
    protected $routers = NULL;

    public function __construct()
    {
        $this->routers = new \SplPriorityQueue();
        $this->routers->SetExtractFlags(\SplPriorityQueue::EXTR_DATA);
    }

    // Adds a Router with a given priority.
    public function AddRouter($priority, Router $router)
    {
        $this->routers->Insert($router, $priority);
    }

    // This will route the Request and queue any vended tasks on the Pump.
    public function DispatchRequest(Request $request)
    {
        foreach ($this->routers as $router) {
            if ($task = $router->VendTask($request)) {
                $this->pump()->QueueTask($task);
                break;
            }
        }
        if (!$task) {
            throw new DispatcherException('The request could not be completed');
        }
    }

    public function set_pump(TaskPump $pump) { $this->pump = $pump; }
    public function pump()
    {
        if (!$this->pump)
            return TaskPump::Pump();
        return $this->pump;
    }
}

class DispatcherException extends \Exception
{}
