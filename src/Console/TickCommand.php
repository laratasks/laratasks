<?php

namespace Laratasks\Laratasks\Console;

use Illuminate\Console\Command;
//
use Laratasks\Laratasks\Jobs\DispatchScheduledTasks;

class TickCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laratasks:tick';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ticks.';

    /**
     * @return void
     */
    public function handle()
    {
        dispatch(new DispatchScheduledTasks());
    }
}