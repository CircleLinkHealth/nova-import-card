<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CreateMigrationCommand extends Command
{
    const DEFAULT_CLASS_NAME = 'CreateScheduleMonitorTables';

    public $description = 'Create the migration for the db tables';

    public $signature = 'schedule-monitor:create-migration';

    public function camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    public function handle(Filesystem $filesystem)
    {
        $tasksClassName = $this->getTasksTableClassName();
        if (class_exists($tasksClassName)) {
            $this->info('No need to create migration. It already exists.');

            return;
        }

        $stubPath   = __DIR__.'/../../database/migrations/create_schedule_monitor_tables.php.stub';
        $stub       = $filesystem->get($stubPath);
        $migration  = str_replace(self::DEFAULT_CLASS_NAME, $tasksClassName, $stub);
        $targetPath = database_path('migrations/'.date('Y_m_d_His', time()).'_'.$tasksClassName.'.php');
        $filesystem->put($targetPath, $migration);

        $this->line('Done!');
        $this->info('Run `php artisan migrate` to create the DB tables.');
    }

    private function getTasksTableClassName()
    {
        return $this->camelize('create_'.config('schedule-monitor.tasks_db_table'));
    }
}
