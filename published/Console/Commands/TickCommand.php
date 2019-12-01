<?php

// Change this is you use different root namespace
namespace App\Console\Commands;

use Illuminate\Console\Command;
// Change this is you use different root namespace
use App\Jobs\DispatchScheduledTasks;

/**
 * Don't forget to register this command under $commands property
 * in your app/Console/Kernel.php.
 *
 * @package App\Console\Commands
 */
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