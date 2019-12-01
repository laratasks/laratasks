<?php

namespace Laratasks\Laratasks;

use Illuminate\Support\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
//
use Laratasks\Laratasks\Console\TickCommand;
use Laratasks\Laratasks\Exceptions\TaskNotFoundException;

/**
 * Class Task
 *
 * @package Laratasks\Laratasks
 *
 * Database columns
 *
 * @property int $attempts_used
 * @property int $max_attempts
 * @property string $task_type
 * @property string $parent_strategy
 * @property int $id
 *
 * Laravel eloquent relations
 * @property Task[] $parents
 *
 * Laravel accessors
 *
 * @property string $taskType
 * @property int $attemptsUsed
 * @property int $maxAttempts
 * @property int $remainingAttempts
 * @property string $status
 * @property bool $succeed
 * @property string $parentStrategy
 * @property bool $allParentsSucceed
 * @property bool $allParentsFailed
 * @property bool $someParentsFailed
 * @property bool $someParentsSucceed
 *
 * @method static Task query()
 * @method Task ready()
 * @method Task prioritized()
 * @method Task created()
 * @method Task scheduled()
 * @method Task dispatched()
 * @method Task succeed()
 * @method Task failed()
 * @method get()
 * @method increment(string $columnName)
 * @method static Task create(array $data)
 */
class Task extends Model
{
    // ========================= //
    // ===     STATUSES      === //
    // ========================= //

    /**
     * Task has been created and waits to be
     * scheduled for execution.
     */
    public const STATUS_CREATED = 'created';

    /**
     * Task has been scheduled for execution.
     * Now it waits to be dispatched during next tick.
     * @see TickCommand
     */
    public const STATUS_SCHEDULED = 'scheduled';

    /**
     * Scheduled task has been picked by queue worker
     * and has been dispatched as laravel job.
     */
    public const STATUS_DISPATCHED = 'dispatched';

    /**
     * Queue worker has processed dispatched job
     * and task is currently running.
     */
    public const STATUS_RUNNING = 'running';

    /**
     * Task has been successfully finished.
     * It's succeed_at property has been set.
     */
    public const STATUS_SUCCEED = 'succeed';

    /**
     * Tasks marked with this status failed
     * and reached their max_attempts value.
     * These tasks won't be rescheduled again
     * unless explicitly instruct to do so.
     *
     * Dispatches onFailure(int $remainingAttempts) on ExecutableTask class.
     */
    public const STATUS_FAILED = 'failed';

    /**
     * Task failed, but it did not reach it's
     * max_attempts value and will be retried again.
     */
    public const STATUS_FAILED_RETRYING = 'failed_retrying';

    /**
     * These tasks won't be dispatched because of their
     * parent dependencies.
     *
     * If task has 'all_succeed' parent strategy and some of it's parents failed (STATUS_FAILED)
     * or has a 'all_failed' strategy and some of it's parent succeed
     * or has a 'some_succeed' strategy and all of it's parents failed
     * or has a 'some_failed' strategy and all of it's parent succeed
     * it won't be dispatched anymore.
     *
     */
    public const STATUS_UNMET_DEPENDENCIES = 'unmet_dependencies';

    // ============================ //
    // ===  PARENT STRATEGIES   === //
    // ============================ //

    /**
     * Task will be executed only if all of it's parents
     * succeed.
     */
    public const PARENT_STRATEGY_ALL_SUCCEED = 'all_succeed';

    /**
     * Task will be executed only if all of it's parent fail.
     */
    public const PARENT_STRATEGY_ALL_FAILED = 'all_failed';

    /**
     * Task will be executed if some of it's parents succeed
     */
    public const PARENT_STRATEGY_SOME_SUCCEED = 'some_succeed';

    /*
     * Task will be executed if some of it's parents fail
     */
    public const PARENT_STRATEGY_SOME_FAILED = 'some_failed';

    /**
     * Task will be executed without caring about it's parents.
     * It's little bit disrespectful tho.
     */
    public const PARENT_STRATEGY_IGNORE = 'ignore';


    // ========================= //
    // === MODEL ATTRIBUTES  === //
    // ========================= //

    protected $dates = [
        'scheduled_to'
    ];

    // =========================== //
    // ===    RELATIONSHIPS    === //
    // =========================== //

    public function parents()
    {
        return $this->belongsToMany(
            Task::class,
            'task_has_parents',
            'parent_task_id',
            'task_id'
        );
    }

    // ========================= //
    // ===      SCOPES       === //
    // ========================= //

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeCreated(Builder $query)
    {
        return $query->where('status', self::STATUS_CREATED);
    }

    /**
     * @param Builder $query
     * @param Carbon|null $scheduledTo
     * @return Builder
     */
    public function scopeScheduled(Builder $query, Carbon $scheduledTo = null)
    {
        return $query
            ->where('status', self::STATUS_SCHEDULED)
            ->where(
                'scheduled_to',
                '<=',
                $scheduledTo ?? now()
            );
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeDispatched(Builder $query)
    {
        return $query->where('status', self::STATUS_DISPATCHED);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeSucceed(Builder $query)
    {
        return $query->where('status', self::STATUS_SUCCEED);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeFailed(Builder $query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePrioritized(Builder $query)
    {
        return $query->orderByDesc('priority');
    }

    // ========================== //
    // ===     ACCESSORS      === //
    // ========================== //

    /**
     * @return int
     */
    public function getAttemptsUsedAttribute(): int
    {
        return $this->attempts_used;
    }

    /**
     * @return int
     */
    public function getMaxAttemptsAttribute(): int
    {
        return $this->max_attempts;
    }

    /**
     * @return int
     */
    public function getAttemptsRemainingAttribute(): int
    {
        return $this->maxAttempts - $this->attemptsUsed;
    }

    /**
     * @return string
     */
    public function getTaskTypeAttribute()
    {
        return $this->task_type;
    }

    /**
     * @return bool
     */
    public function getSucceedAttribute(): bool
    {
        return $this->status === self::STATUS_SUCCEED;
    }

    /**
     * @return string
     */
    public function getParentStrategy(): string
    {
        return $this->parent_strategy;
    }

    /**
     * @return bool
     */
    public function getAllParentsSucceedAttribute(): bool
    {
        /** @var Task[] $parentTasks */
        $parentTasks = $this->parents;

        foreach ($parentTasks as $parentTask) {
            if ($parentTask->status !== self::STATUS_SUCCEED) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function getAllParentsFailedAttribute(): bool
    {
        /** @var Task[] $parentTasks */
        $parentTasks = $this->parents;

        foreach ($parentTasks as $parentTask) {
            if ($parentTask->status !== self::STATUS_FAILED) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function getSomeParentsFailedAttribute(): bool
    {
        /** @var Task[] $parentTasks */
        $parentTasks = $this->parents;

        foreach ($parentTasks as $parentTask) {
            if ($parentTask->status === self::STATUS_FAILED) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getSomeParentsSucceedAttribute(): bool
    {
        /** @var Task[] $parentTasks */
        $parentTasks = $this->parents;

        foreach ($parentTasks as $parentTask) {
            if ($parentTask->status === self::STATUS_SUCCEED) {
                return true;
            }
        }

        return false;
    }

    // ========================= //
    // ===       MISC        === //
    // ========================= //

    /**
     * @param string $taskType
     * @param Carbon|null $to
     * @throws TaskNotFoundException
     */
    public static function schedule(string $taskType, Carbon $to = null)
    {
        if (!class_exists($taskType)) {
            throw new TaskNotFoundException(
                sprintf(
                    'Can not instantiate task %s, class does not exist.',
                    $taskType
                )
            );
        }

        $task = new self();
        $taskJob = ExecutableTask::create($taskType, $task);

        $task->update([
            'task_type' => $taskType,
            'priority' => $taskJob->getPriority() ?? config('laratasks.default_priority'),
            'max_attempts' => $taskJob->getMaxAttempts() ?? config('laratasks.default_max_attempts'),
            'schedule_to' => $to ?? now(),
            'resource' => $taskJob->getResource(),
            'parent_strategy' => $taskJob->getParentStrategy() ?? config('laratasks.default_parent_strategy'),
            'queue' => $taskJob->getQueue() ?? config('laratasks.default_queue'),
            'parameters' => '{}',
            'message' => '{}'
        ]);
    }

    /**
     * @param string $taskType
     * @return TaskBuilder
     */
    public static function build(string $taskType): TaskBuilder
    {
        return new TaskBuilder($taskType);
    }
}