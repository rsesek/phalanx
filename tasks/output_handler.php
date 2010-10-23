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

namespace phalanx\tasks;

// The OutputHandler is invoked by the TaskPump once all tasks have been
// processed. The job of this class is to take the fired tasks, extract their
// output, 
abstract class OutputHandler
{
    // Called by the TaskPump when all tasks have finished processing.
    public function Start()
    {
        $this->_DoStart();
    }

    // Subclasses should implement this method to perform their actual output
    // handling. The TaskPump will call Start(), which sets up the object's
    // state before calling _DoStart().
    abstract protected function _DoStart();

    // Returns a PropertyBag of data from |$task| based on its output list.
    public function GetTaskData(Task $task)
    {
        $data        = new \phalanx\base\PropertyBag();
        $output_list = $task::OutputList();
        $output_list[] = 'input';
        foreach ($output_list as $key)
        {
            $class = new \ReflectionClass(get_class($task));
            if ($class->HasProperty($key) && $class->GetProperty($key)->IsPublic())
                $data->Set($key, $task->$key);
            else if ($class->HasMethod($key) && $class->GetMethod($key)->IsPublic())
                $data->Set($key, $task->$key());
        }
        return $data;
    }
}
