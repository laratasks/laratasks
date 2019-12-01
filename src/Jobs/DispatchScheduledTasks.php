<?php

namespace Laratasks\Laratasks\Jobs;

//
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
//
use Laratasks\Laratasks\Task;
use Laratasks\Laratasks\ExecutableTask;

class DispatchScheduledTasks implements ShouldQueue
{
    use Queueable;
    use Dispatchable;
    use SerializesModels;
    use InteractsWithQueue;

    /**
     * @return void
     */
    public function handle()
    {
        /** @var Task[] $scheduledTasks */
        $scheduledTasks = Task::query()
            ->scheduled()
            ->prioritized()
            ->get();

        foreach ($scheduledTasks as $task) {
            dispatch(new ExecuteTask(
                ExecutableTask::create($task->taskType, $task)
            ));
        }
    }
}