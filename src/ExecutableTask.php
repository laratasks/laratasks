<?php

namespace Laratasks\Laratasks;

use Laratasks\Laratasks\Exceptions\UnknownTaskStatusException;

/**
 * Class ProcessTask
 * @package Laratasks\Laratasks\Jobs
 * @method static void dispatch(Task $task)
 * @property Task $task
 */
abstract class ExecutableTask
{

    /** @var Task */
    public $task;

    /**
     * @var int
     */
    protected $priority = null;

    /**
     * @var int
     */
    protected $maxAttempts = null;

    /**
     * @var string
     */
    protected $parentStrategy = null;

    /**
     * @var string
     */
    protected $queue = null;

    /**
     * @return mixed
     */
    abstract public function execute();

    /**
     * @return array
     */
    abstract public function setup(): array;

    /**
     * @return mixed
     */
    abstract public function onSchedule();

    /**
     * @return mixed
     */
    abstract public function onDispatch();

    /**
     * This method is called when dispatched task
     * is evaluated as not ready for execution
     * (method isReady returned false).
     * @return mixed
     */
    abstract public function onPostpone();

    /**
     * Method called when task status is set
     * to STATUS_UNMET_DEPENDENCIES
     * @return mixed
     */
    abstract public function onDiscard();

    /**
     * Method called when task status is set
     * to STATUS_RUNNING
     * @return mixed
     */
    abstract public function onStart();

    /**
     * @return mixed
     */
    abstract public function onSuccess();

    /**
     * @param int $remainingAttempts
     * @return mixed
     */
    abstract public function onFailure(int $remainingAttempts);

    /**
     * If this method returns false, task will be skipped.
     * It is a good place for checking some external preconditions
     * that can not be decided on database level.
     * @return bool
     */
    abstract public function isReady(): bool;

    /**
     * Resource is identifier of underlying resource that Task
     * needs to use. By default Laratasks will group tasks by
     * their resources meaning only single task of given resource
     * can be executed at once.
     *
     * This mechanism can be used to prevent unnecessary dispatching of
     * jobs that would be postponed or would failed anyway.
     *
     * E.g: Resource could be name of file that you would write in given
     * task to. It makes sense to check in isReady method if the file is locked
     * by another process, but via resource attribute you can prevent dispatching
     * the tasks even earlier.
     *
     * @return string
     */
    abstract public function getResource(): string;

    /**
     * ExecutableTask constructor.
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * @param string $taskType
     * @param Task $task
     * @return ExecutableTask
     */
    public static function create(string $taskType, Task $task): ExecutableTask
    {
        /** @var ExecutableTask $task */
        $taskJob = app()->makeWith($taskType, compact('task'));

        if (!is_subclass_of($taskJob, self::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Task %s does not extend class %s',
                $taskType,
                self::class
            ));
        }

        return $taskJob;
    }

    /**
     * @throws UnknownTaskStatusException
     */
    public function onUpdate()
    {
        $task = $this->task;

        switch ($task->status) {
            case Task::STATUS_CREATED:
                TaskLog::info('Task created', $task);
                break;

            case Task::STATUS_SCHEDULED:
                TaskLog::info('Task scheduled', $task);
                $this->onSchedule();
                break;

            case Task::STATUS_DISPATCHED:
                TaskLog::info('Task dispatched', $task);
                $this->onDispatch();
                break;

            case Task::STATUS_RUNNING:
                TaskLog::info('Task started', $task);
                $this->onStart();
                break;

            case Task::STATUS_SUCCEED:
                TaskLog::info('Task succeed', $task);
                $this->onSuccess();
                break;

            case Task::STATUS_FAILED_RETRYING:
            case Task::STATUS_FAILED:
                TaskLog::warning('Task failed', $task);
                $this->onFailure(
                    $this->task->remainingAttempts
                );
                break;

            case Task::STATUS_UNMET_DEPENDENCIES:
                TaskLog::notice('Task discarded', $task);
                $this->onDiscard();
                break;

            default:
                throw new UnknownTaskStatusException(sprintf(
                    'Value %s is not recognized as accepted Task status',
                    $this->task->status
                ));
        }
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return int
     */
    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * @return string
     */
    public function getParentStrategy(): string
    {
        return $this->parentStrategy;
    }

    /**
     * @return string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }
}
