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

namespace phalanx\events;
use \phalanx\views\View as View;

require_once PHALANX_ROOT . '/events/event_pump.php';
require_once PHALANX_ROOT . '/events/output_handler.php';
require_once PHALANX_ROOT . '/views/view.php';

// This implementation of OutputHandler uses the Views system to present
// output. This class requires a Lambda function to transform the last-
// processed event's name into a template name.
class ViewOutputHandler extends OutputHandler
{
    // The function that transforms an event name into a template name.
    protected $template_loader;

    protected function _DoStart()
    {
        if (EventPump::Pump()->GetEventChain()->Count() > 0)
        {
            $event    = EventPump::Pump()->GetEventChain()->Top();
            $loader   = $this->template_loader;
            $tpl_name = $loader(get_class($event));
            $data     = $this->GetEventData($event);

            $view     = new View($tpl_name);
            $keys     = $data->AllKeys();
            foreach ($keys as $key)
                $view->$key = $data->$key;

            $view->Render();
        }
    }

    // Getters and setters.
    // ------------------------------------------------------------------------
    public function set_template_loader(\Closure $loader) { $this->template_loader = $loader; }
    public function template_loader() { return $this->template_loader; }
}
