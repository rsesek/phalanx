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

// The TaskPump is responsible for running work in the execution flow of Task-
// based architecture. Task objects are queued for running by various
// components, primarily the Executor. Work does not get done until the pump
// is told to Loop. 
class TaskPump
{
    // The shared task pump object.
    private static $pump;

    // The OutputHandler instance for the pump.
    protected $output_handler = NULL;

    // An SplQueue of the tasks that were registered wtih QueueTask() but are
    // waiting for the current task to finish.
    protected $work_queue = NULL;

    // A SplStack of Task objects. The stack is a history of Task execution,
    // including those that got canceled.
    protected $tasks = NULL;

    // The task that is currently executing. Will be NULL if there is no such
    // task.
    protected $current_task = NULL;

    // The task that is scheduled to run as priority work on the next cycle of
    // the loop.
    protected $next_task = NULL;

    // Constructor. Do not use directly. Use TaskPump::Pump().
    public function __construct()
    {
        $this->work_queue = new \SplQueue();
        $this->tasks      = new \SplStack();
    }

    // Schedules a task to be run. The Task will be processed in the order it
    // came in.
    public function QueueTask(Task $task)
    {
        $this->work_queue->Push($task);
    }

    // Schedules the |$task| as priority work, which executes before queued
    // work. This method is meant to be used when a Task is running and intends
    // to replace itself with some other work. It is unadvised to call this when
    // the loop isn't running (use QueueTask instead).
    // Note: The current task MUST return IF the |$task| is to run as priority:
    //   public FooTask extends Task {
    //     public function Run() {
    //       // ...
    //       if (!$condition) {
    //         // ** RETURNING IS CRITICAL **
    //         return TaskPump::Pump()->RunTask(new BarTask($this->request));
    //       }
    //     }
    //   }
    //
    public function RunTask(Task $task)
    {
        // If there isn't a current_task, the loop isn't running so just
        // schedule this at the front for when it is started (bypassing any
        // existing work). If the loop is running but next_task is set, the
        // caller did not return from the current task, so schedule this at the
        // front of the queue. This in essense turns the queued priority work
        // into a stack if this is called multiple times without the caller
        // returning. Depending on the work, this could lead to "priority
        // inversion."
        if (!$this->current_task || $this->next_task)
            $this->work_queue->Unshift($task);

        // The loop is running with work from the work_queue. Assuming the
        // current_task returned after calling this, next_task will get serviced
        // immediately after control returns to the loop.
        $this->next_task = $task;
    }

    // Runs the internal loop, pumping work from the queue and running it.
    // Currently this does not have reentrancy protection; it is UNSAFE to call
    // Loop() from within a Task that is running in the loop.
    public function Loop($keep_running = FALSE)
    {
        for (;;) {
            $did_work = FALSE;

            // If there's a next_task that preemted another Task, run it now.
            if ($this->next_task) {
                // Clear next_task in case |$task| makes a call to RunTask.
                $task = $this->next_task;
                $this->next_task = NULL;
                $this->_RunTask($task);
                $did_work = TRUE;
            }

            // Handle queued work once per loop iteration to ensure that
            // priority work gets serviced.
            if ($this->work_queue->Count() > 0) {
                $this->_RunTask($this->work_queue->Pop());
                $did_work = TRUE;
            }

            // If an entire iteration of the loop passed without doing any work,
            // and the loop isn't supposed to run indefinitely, start output
            // handling.
            if (!$did_work && !$keep_running) {
                $this->StopPump();
                throw new TaskPumpException('TaskPump::StopPump has left the Loop running');
            }
        }
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

    // Cancels the given Task and will begin processing the next deferred
    // task. If no other deferred tasks exist, output handling begins.
    // When calling |$task->Cancel()|, which calls this, the task MUST return
    // immediately. See the comment at RunTask() for example code.
    public function Cancel(Task $task)
    {
        $task->set_cancelled();
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
        return $this->work_queue;
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
