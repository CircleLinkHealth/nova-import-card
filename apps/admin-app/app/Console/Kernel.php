<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console;

use App\Console\Commands\CheckVoiceCalls;
use CircleLinkHealth\Core\Console\Commands\CheckEmrDirectInbox;
use CircleLinkHealth\Eligibility\Console\ProcessNextEligibilityBatchChunk;
use CircleLinkHealth\Eligibility\Jobs\OverwritePatientMrnsFromSupplementalData;
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
        CheckEmrDirectInbox::class,
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
        $schedule->command(CheckEmrDirectInbox::class)
            ->everyFiveMinutes();

        $schedule->job(OverwritePatientMrnsFromSupplementalData::class)
            ->everyThirtyMinutes();

        $schedule->command(ProcessNextEligibilityBatchChunk::class)
            ->everyFiveMinutes()
            ->withoutOverlapping();

//        $schedule->command(CheckVoiceCalls::class, [now()->subHour()])
//            ->hourly()
//            ->between('7:00', '23:00');
    }
}
