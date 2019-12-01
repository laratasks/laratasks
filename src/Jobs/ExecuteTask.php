<?php

namespace Laratasks\Laratasks\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
//
use Laratasks\Laratasks\Task;
use Laratasks\Laratasks\TaskLog;
use Laratasks\Laratasks\ExecutableTask;

class ExecuteTask implements ShouldQueue
{
    use Queueable;
    use Dispatchable;
    use SerializesModels;
    use InteractsWithQueue;

    /**
     * @var ExecutableTask $task
     */
    public $taskJob;

    public function __construct(ExecutableTask $taskJob)
    {
        $this->taskJob = $taskJob;

        $taskJob->task->update([
            'status' => Task::STATUS_DISPATCHED
        ]);
    }

    public function handle()
    {
        /** @var Task $task */
        $task = $this->taskJob->task;

        if (!$this->taskJob->isReady()) {
            TaskLog::info('Task is not ready, postponing.', $task);
            $this->taskJob->onPostpone();
            return;
        }

        if ($this->askParents($task) === false) {
            TaskLog::info('Parent results do not allow tasks to be executed, skipping', $task);
            return;
        }

        $parameters = $this->taskJob->setup();

        $task->update([
            'attempts_used' => $task->attemptsUsed + 1,
            'parameters' => json_encode($parameters)
        ]);

        $this->taskJob->execute();
    }

    /**
     * The job failed to process.
     *
     * @see https://laravel.com/docs/5.8/queues#dealing-with-failed-jobs
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        $remainingAttempts = $this->taskJob->task->remainingAttempts;

        $data = [
            'failed_at' => now(),
            'message' => json_encode($exception->getMessage())
        ];

        if ($remainingAttempts === 0) {
            $data['status'] = Task::STATUS_FAILED;
        } else {
            $data['status'] = Task::STATUS_FAILED_RETRYING;
        }


        $this->taskJob->task->update($data);
    }

    protected function askParents(Task $task)
    {
        switch ($task->parentStrategy) {
            case Task::PARENT_STRATEGY_ALL_SUCCEED:
                return $task->allParentsSucceed;

            case Task::PARENT_STRATEGY_ALL_FAILED:
                return $task->allParentsFailed;

            case Task::PARENT_STRATEGY_SOME_FAILED:
                return $task->someParentsFailed;

            case Task::PARENT_STRATEGY_SOME_SUCCEED:
                return $task->someParentsSucceed;

            case Task::PARENT_STRATEGY_IGNORE:
                return true;

            default:
                return false;
        }
    }
}
