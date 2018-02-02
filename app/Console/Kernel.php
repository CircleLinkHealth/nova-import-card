<?php namespace App\Console;

use App\Console\Commands\Athena\GetAppointments;
use App\Console\Commands\Athena\GetCcds;
use App\Console\Commands\AttachBillableProblemsToLastMonthSummary;
use App\Console\Commands\CheckEmrDirectInbox;
use App\Console\Commands\DeleteProcessedFiles;
use App\Console\Commands\EmailRNDailyReport;
use App\Console\Commands\QueueGenerateNurseInvoices;
use App\Console\Commands\QueueSendAuditReports;
use App\Console\Commands\RemoveScheduledCallsForWithdrawnAndPausedPatients;
use App\Console\Commands\RescheduleMissedCalls;
use App\Console\Commands\ResetCcmTime;
use App\Console\Commands\SyncFamilialCalls;
use App\Console\Commands\TuneScheduledCalls;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(RescheduleMissedCalls::class)->dailyAt('00:05');

        $schedule->command(TuneScheduledCalls::class)->dailyAt('00:20');

//        $schedule->call(function () {
//            (new EnrollmentSMSSender())->exec();
//        })->dailyAt('13:00');

        $schedule->command(SyncFamilialCalls::class)->dailyAt('00:30');

        //Removes All Scheduled Calls for patients that are withdrawn
        $schedule->command(RemoveScheduledCallsForWithdrawnAndPausedPatients::class)->everyMinute();

//        $schedule->command(EmailWeeklyReports::class, ['--practice', '--provider'])
//                 ->weeklyOn(1, '10:00');

        $schedule->command('emailapprovalreminder:providers')
                 ->weekdays()
                 ->dailyAt('08:00');

        //commenting out due to isues with google calendar
//        $schedule->command('nurseSchedule:export')
//                 ->hourly();

        $schedule->command(GetAppointments::class)
                 ->dailyAt('23:00');

        $schedule->command(GetCcds::class)
                 ->everyThirtyMinutes();

        $schedule->command(EmailRNDailyReport::class)
                 ->weekdays()
                 ->at('21:00');

        //Run at 12:01am every 1st of month
        $schedule->command(ResetCcmTime::class)
                 ->cron('1 0 1 * *');

        //Run at 12:30am every 1st of month
        $schedule->command(AttachBillableProblemsToLastMonthSummary::class)
                 ->cron('30 0 1 * *');

//        $schedule->command('lgh:importInsurance')
//            ->dailyAt('05:00');

        $schedule->command(QueueGenerateNurseInvoices::class)
                 ->dailyAt('04:00')
                 ->withoutOverlapping();
                 
        $schedule->command(\App\Console\Commands\CareplanEnrollmentAdminNotification::class)
                ->dailyAt('09:00')
                ->withoutOverlapping();

        $schedule->command('report:nurseDaily')
                 ->dailyAt('23:50')
                 ->withoutOverlapping();

//        $schedule->command('ccda:toJson')
//            ->everyMinute()
//            ->withoutOverlapping();

        $schedule->command('ccda:determineEligibility')
                 ->everyFiveMinutes()
                 ->withoutOverlapping();

//        $schedule->command('ccda:process')
//            ->everyMinute()
//            ->withoutOverlapping();

        //every 2 hours
//        $schedule->command('ccdas:split-merged')
//            ->cron('0 */2 * * *');

        $schedule->command(QueueSendAuditReports::class)
                 ->monthlyOn(1, '02:00');

        $schedule->command(CheckEmrDirectInbox::class)
                 ->everyFiveMinutes()
                 ->withoutOverlapping();

        $schedule->command(DeleteProcessedFiles::class)
                 ->everyThirtyMinutes()
                 ->withoutOverlapping();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
