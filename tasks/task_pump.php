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

// User interaction and other functions produce tasks, which are raised and
// registered with the TaskPump. The pump executes tasks as they come in, and
// the last non-cancelled task is usually the one whose output is rendered.
class TaskPump
{
    // The shared task pump object.
    private static $pump;

    // The OutputHandler instance for the pump.
    protected $output_handler = NULL;

    // An SplQueue of the tasks that were registered wtih QueueTask() but are
    // waiting for the current task to finish.
    protected $task_queue = NULL;

    // A SplStack of Task objects. The stack is a history of Task execution,
    // including those that got canceled.
    protected $tasks = NULL;

    // The task that is currently executing. Will be NULL if there is no such
    // task.
    protected $current_task = NULL;

    // Constructor. Do not use directly. Use TaskPump::Pump().
    public function __construct()
    {
        $this->task_queue = new \SplQueue();
        $this->tasks      = new \SplStack();
    }

    // Schedules a task to be run. If another task is currently being fired,
    // this will wait until that task is done. If no tasks are currently
    // running, the task will fire immediately.
    public function QueueTask(Task $task)
    {
        // There is already a task executing. Push this new task into the
        // deferred worke queue.
        if ($this->current_task)
        {
            $this->task_queue->Push($task);
            return;
        }

        $this->_RunTask($task);

        $this->_DoDeferredTasks();
    }

    // Preempts any currently executing task and preempts it with this task.
    // |$task| will begin processing immediately. The other task will
    // resume afterwards.
    public function RunTask(Task $task)
    {
        if ($this->current_task)
            $this->task_queue->Push($this->current_task);

        $this->_RunTask($task);

        if ($this->task_queue->Count())
            $this->current_task = $this->task_queue->Pop();

        $this->_DoDeferredTasks();
    }

    // This function does the bulk of the task processing work. Note that
    // this will clobber the |$this->current_task|. Caller is responsible for
    // ensuring it is safe to call this function.
    protected function _RunTask(Task $task)
    {
        $this->current_task = $task;

        $this->tasks->Push($task);
        $task->Run();

        $this->current_task = NULL;
    }

    // If there are no tasks currently processing, this will process all the
    // tasks in the deferred queue.
    protected function _DoDeferredTasks()
    {
        if ($this->current_task)
            return;

        while ($this->task_queue->Count() > 0)
            $this->_RunTask($this->task_queue->Pop());
    }

    // Cancels the given Task and will begin processing the next deferred
    // task. If no other deferred tasks exist, output handling begins.
    public function Cancel(Task $task)
    {
        $task->set_cancelled();
        if ($this->current_task == $task) {
            if ($this->task_queue->Count() == 0) {
                $this->StopPump();
            } else {
                $this->current_task = NULL;
                $this->_DoDeferredTasks();
            }
        }
    }

    // Calling this function will prtask any tasks registered with
    // QueueTask() from being run. A common use for this is registering an
    // task with RunTask() and then stopping any future work from happening
    // using this method.
    public function CancelDeferredTasks()
    {
        while ($this->task_queue->Count() > 0)
            $this->task_queue->Dequeue()->Cancel();
    }

    // Tells the pump to stop pumping tasks and to begin output handling. This
    // will call the current task's Cleanup() function.
    public function StopPump()
    {
        $this->output_handler->Start();
        $this->_Exit();
    }

    // Halts execution of the pump immediately without performing any task
    // cleanup. |$message| will be displayed as output.
    public function Terminate($message)
    {
        echo $message;
        $this->_Exit();
    }

    // Gets the currently executing Task.
    public function GetCurrentTask()
    {
        return $this->current_task;
    }

    // Returns the queue of Tasks that have been registered with QueueTask()
    // and are waiting to run.
    public function GetDeferredTasks()
    {
        return $this->task_queue;
    }

    // Returns the SplStack of tasks that have been fired, in the order they
    // fired. Note that this will NOT contain the current_task until AFTER
    // Cleanup() is called from _QueueTask().
    public function GetTaskHistory()
    {
        $chain = new \SplStack();
        // If we traverse in order, then we preserve the order that tasks
        // completed successfully.
        foreach ($this->tasks as $task) {
            if (!$task->is_cancelled()) {
                $chain->Unshift($task);
            }
        }
        return $chain;
    }

    // Returns |$this->tasks| as a stack. Note that tasks will likely appear
    // multiple times in this stack. The occurrence count corresponds to which
    // states the task has passed through.
    public function GetAllTasks()
    {
        return clone $this->tasks;
    }

    // Internal wrapper around exit() that we can mock.
    protected function _Exit()
    {
        exit;
    }

    // Getters and setters.
    // -------------------------------------------------------------------------

    // Returns the shared TaskPump.
    static public function Pump()
    {
        if (!self::$pump)
            self::set_pump(new TaskPump());
        return self::$pump;
    }
    static public function set_pump(TaskPump $pump) { self::$pump = $pump; }

    public function set_output_handler(OutputHandler $handler) { $this->output_handler = $handler; }
    public function output_handler() { return $this->output_handler; }

    // Testing methods. These are not for public consumption.
    static public function T_set_pump($pump) { self::$pump = $pump; }
}

class TaskPumpException extends \Exception
{
}
