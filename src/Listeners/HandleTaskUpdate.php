<?php

namespace Laratasks\Laratasks\Listeners;

use Throwable;
use Illuminate\Support\Facades\Log;
//
use Laratasks\Laratasks\Task;
use Laratasks\Laratasks\TaskLog;
use Laratasks\Laratasks\ExecutableTask;
use Laratasks\Laratasks\Events\TaskUpdated;
use Laratasks\Laratasks\Jobs\DispatchScheduledTasks;


class HandleTaskUpdate
{
    /**
     * Handle the event.
     *
     * @param TaskUpdated $event
     * @return void
     * @throws Throwable
     */
    public function handle(TaskUpdated $event)
    {
        /** @var Task $task */
        $task = $event->task;

        TaskLog::debug('Task has been updated', $task);

        if ($task->taskType === null) {
            Log::debug('Task is empty so it will not be processed', $task);

            return;
        }

        ExecutableTask::create($task->taskType, $task)->onUpdate();

        if ($task->succeed) {
            dispatch(new DispatchScheduledTasks());
        }
    }
}
