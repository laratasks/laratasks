<?php

namespace Laratasks\Laratasks;

use Illuminate\Support\ServiceProvider;
//
use Laratasks\Laratasks\Console\TickCommand;
use Laratasks\Laratasks\Console\ShowTaskLogCommand;
use Laratasks\Laratasks\Console\ListScheduledTasksCommand;

/**
 * Class LaratasksServiceProvider
 * @package Laratasks\Laratasks
 */
class LaratasksServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrations();
        $this->loadCommands();

        $this->offerConfiguration();
        $this->offerMigrations();
        $this->offerDispatcher();
    }

    protected function offerConfiguration()
    {
        $this->publishes([
            __DIR__.'/../config/laratasks.php' => config_path('laratasks.php')
        ], 'config');
    }

    protected function offerMigrations()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    protected function offerDispatcher()
    {
        $this->publishes([
            __DIR__.'/Jobs/DispatchScheduledTasks.php' => app_path('Jobs/DispatchScheduledTasks.php')
        ], 'migrations');
    }

    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function loadCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TickCommand::class,
                ShowTaskLogCommand::class,
                ListScheduledTasksCommand::class,
            ]);
        }
    }
}