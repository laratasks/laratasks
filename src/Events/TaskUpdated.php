<?php

namespace Laratasks\Laratasks\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
//
use Laratasks\Laratasks\Task;

class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use SerializesModels;
    use InteractsWithSockets;

    /**
     * @var Task
     */
    public $task;

    /**
     * Create a new event instance.
     *
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('laratasks.task.' . $this->task->id);
    }
}
