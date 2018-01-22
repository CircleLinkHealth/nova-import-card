<?php namespace App\Console;

use App\Algorithms\Calls\ReschedulerHandler;
use App\Console\Commands\AttachBillableProblemsToLastMonthSummary;
use App\Console\Commands\CheckEmrDirectInbox;
use App\Console\Commands\DeleteProcessedFiles;
use App\Console\Commands\QueueSendAuditReports;
use App\Services\Calls\SchedulerService;
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
        //Reconciles missed calls and creates a new call for patient using algo
        $schedule->call(function () {

            $handled = (new ReschedulerHandler())->handle();

            if ( ! empty($handled)) {
                $message = "The CPMbot just rescheduled some calls.\n";

                foreach ($handled as $call) {
                    $message = "We just fixed call: {$call->id}. \n";
                }

                sendSlackMessage('#background-tasks', $message);
            }
        })->dailyAt('00:05');

        //tunes scheduled call dates.
        $schedule->call(function () {
            (new SchedulerService())->tuneScheduledCallsWithUpdatedCCMTime();
        })->dailyAt('00:20');

//        $schedule->call(function () {
//            (new EnrollmentSMSSender())->exec();
//        })->dailyAt('13:00');

        //syncs families.
        $schedule->call(function () {
            (new SchedulerService())->syncFamilialCalls();
        })->dailyAt('00:30');

        //Removes All Scheduled Calls for patients that are withdrawn
        $schedule->call(function () {
            (new SchedulerService())->removeScheduledCallsForWithdrawnAndPausedPatients();
        })->everyMinute();

//        $schedule->command(EmailWeeklyReports::class, ['--practice', '--provider'])
//                 ->weeklyOn(1, '10:00');

        $schedule->command('emailapprovalreminder:providers')
                 ->weekdays()
                 ->dailyAt('08:00');

        $schedule->command('nurseSchedule:export')
                 ->hourly();

        $schedule->command('athena:getAppointments')
                 ->dailyAt('23:00');

        $schedule->command('athena:getCcds')
                 ->everyThirtyMinutes();

        $schedule->command('nurses:emailDailyReport')
                 ->weekdays()
                 ->at('21:00');

        //Run at 12:01am every 1st of month
        $schedule->command('ccm_time:reset')
                 ->cron('1 0 1 * *');

        //Run at 12:30am every 1st of month
        $schedule->command(AttachBillableProblemsToLastMonthSummary::class)
                 ->cron('30 0 1 * *');

//        $schedule->command('lgh:importInsurance')
//            ->dailyAt('05:00');

        $schedule->command('report:nurseInvoices')
                 ->dailyAt('04:00')
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
