<?php

// Change this is you use different root namespace
namespace App\Jobs;

use Laratasks\Laratasks\Jobs\DispatchScheduledTasks as Dispatcher;

class DispatchScheduledTasks extends Dispatcher
{
    /**
     * Change the default behaviour of fetching
     * scheduled tasks overriding this method.
     *
     * @return array
     */
    protected function getTasks(): array
    {
        return parent::getTasks();
    }
}