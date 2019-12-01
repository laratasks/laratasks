<?php

namespace Laratasks\Laratasks\Console;

use Illuminate\Console\Command;

class ShowTaskLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laratasks:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show all log history.';

    public function handle()
    {
        // TODO: Add options for sorting, filtering and continuous watch
    }
}
