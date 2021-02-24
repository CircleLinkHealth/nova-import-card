<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console;

use CircleLinkHealth\SelfEnrollment\Console\Commands\EnrollmentFinalAction;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SendSelfEnrollmentReminders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
    ];

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('schedule-monitor:sync')
            ->dailyAt('04:56')
            ->doNotMonitor();

        $schedule->command('schedule-monitor:clean')
            ->daily()
            ->doNotMonitor();

        $schedule->command(SendSelfEnrollmentReminders::class, ['--enrollees'])
            ->dailyAt('10:27');

        $schedule->command(EnrollmentFinalAction::class)
            ->dailyAt('08:27');
    }
}
