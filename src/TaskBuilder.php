<?php

namespace Laratasks\Laratasks;

use Illuminate\Support\Carbon;

class TaskBuilder
{
    /**
     * @var string
     */
    private $taskType;

    /**
     * @var int
     */
    private $priority = null;

    /**
     * @var int
     */
    private $maxAttempts = null;

    /**
     * @var string
     */
    private $parentStrategy = null;

    /**
     * @var string
     */
    private $queue = null;

    /**
     * @var string
     */
    private $resource = '';

    public function __construct(string $taskType)
    {
        $this->taskType = $taskType;
    }

    public function priority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function maxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    public function schedule(Carbon $to = null)
    {
        Task::create([
            'task_type' => $this->taskType,
            'priority' => $this->priority ?? config('laratasks.default_priority'),
            'max_attempts' => $this->maxAttempts ?? config('laratasks.default_max_attempts'),
            'scheduled_to' => $to ?? now(),
            'resource' => $this->resource,
            'parent_strategy' => $this->parentStrategy ?? config('laratasks.default_parent_strategy'),
            'queue' => $this->queue ?? config('laratasks.default_queue'),
            'parameters' => '{}',
            'message' => '{}'
        ]);
    }
}
