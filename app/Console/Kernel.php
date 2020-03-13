<?php

namespace App\Console;

use App\Console\Commands\RunScheduler;
use App\Console\Commands\SendHraSurveyReminder;
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
        RunScheduler::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //set the dayPrior field to aid with QAing

        $isProduction = config('app.env') === 'production';

        $schedule->command(SendHraSurveyReminder::class, [
            'daysPrior' => $isProduction
                ? 10
                : 2,
        ])->dailyAt('09:00')->onOneServer();

        $schedule->command(SendHraSurveyReminder::class, [
            'daysPrior' => $isProduction
                ? 8
                : 1,
        ])->dailyAt('09:05')->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        $this->load(__DIR__ . '/PersonalizedPreventionPlanController');

        require base_path('routes/console.php');
    }
}
