<?php

namespace Laratasks\Laratasks\Jobs;

//
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
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
     * @return Collection
     */
    protected function getTasks(): Collection
    {
        /** @var Collection $scheduledTasks */
        $scheduledTasks = Task::query()
            ->scheduled()
            ->prioritized()
            ->get();

        return $scheduledTasks;
    }

    /**
     * @return void
     */
    final public function handle()
    {
        foreach ($this->getTasks() as $task) {
            dispatch(new ExecuteTask(
                ExecutableTask::create($task->task_type, $task)
            ));
        }
    }
}
