<?php

namespace Laratasks\Laratasks;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TaskLog
 * @package Laratasks\Laratasks
 * @method static TaskLog create(array $data)
 */
class TaskLog extends Model
{
    /**
     * Log levels as described in psr-3
     * @see https://www.php-fig.org/psr/psr-3/
     */
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    /**
     * @param string $message
     * @param Task $task
     * @param string $logLevel
     * @param array $context
     */
    public static function log(string $message, Task $task, string $logLevel, array $context = [])
    {
        self::create([
            'task_id' => $task->id,
            'task_type' => $task->taskType,
            'message' => $message,
            'log_level' => $logLevel,
            'context' => json_encode($context),
            'created_at' => now()
        ]);
    }

    /**
     * @param string $message
     * @param Task $task
     * @param array $context
     */
    public static function debug(string $message, Task $task, array $context = [])
    {
        self::log($message, $task, self::DEBUG, $context);
    }

    /**
     * @param string $message
     * @param Task $task
     * @param array $context
     */
    public static function info(string $message, Task $task, array $context = [])
    {
        self::log($message, $task, self::INFO, $context);
    }

    /**
     * @param string $message
     * @param Task $task
     * @param array $context
     */
    public static function notice(string $message, Task $task, array $context = [])
    {
        self::log($message, $task, self::NOTICE, $context);
    }

    /**
     * @param string $message
     * @param Task $task
     * @param array $context
     */
    public static function warning(string $message, Task $task, array $context = [])
    {
        self::log($message, $task, self::WARNING, $context);
    }

    /**
     * @param string $message
     * @param Task $task
     * @param array $context
     */
    public static function error(string $message, Task $task, array $context = [])
    {
        self::log($message, $task, self::ERROR, $context);
    }
}