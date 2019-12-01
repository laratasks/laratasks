<?php

namespace Laratasks\Laratasks\Console;

use Illuminate\Console\Command;

class ListScheduledTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laratasks:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show all scheduled tasks in table.';

    /**
     * @return void
     */
    public function handle()
    {
        // TODO: Add options to sort tasks by (priority, created_at, already tried, etc?)
        // TODO: --monitor (refresh every 1,2,3s?)
    }
}
