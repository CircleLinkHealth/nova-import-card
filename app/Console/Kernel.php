<?php namespace App\Console;

use App\Algorithms\Calls\ReschedulerHandler;
use App\Console\Commands\Athena\GetAppointments;
use App\Console\Commands\Athena\GetCcds;
use App\Console\Commands\CheckEmrDirectInbox;
use App\Console\Commands\EmailRNDailyReport;
use App\Console\Commands\EmailsProvidersToApproveCareplans;
use App\Console\Commands\EmailWeeklyReports;
use App\Console\Commands\ExportNurseSchedulesToGoogleCalendar;
use App\Console\Commands\FormatLocationPhone;
use App\Console\Commands\GeneratePatientReports;
use App\Console\Commands\ImportLGHInsurance;
use App\Console\Commands\ImportNurseScheduleFromGoogleCalendar;
use App\Console\Commands\Inspire;
use App\Console\Commands\MapSnomedToCpmProblems;
use App\Console\Commands\NukeItemAndMeta;
use App\Console\Commands\ProcessCcdaLGHMixup;
use App\Console\Commands\QueueCcdasToConvertToJson;
use App\Console\Commands\QueueCcdasToProcess;
use App\Console\Commands\QueueCcdaToDetermineEnrollmentEligibility;
use App\Console\Commands\QueueSendAuditReports;
use App\Console\Commands\RecalculateCcmTime;
use App\Console\Commands\ResetCcmTime;
use App\Console\Commands\SplitMergedCcdas;
use App\Reports\WeeklyReportDispatcher;
use App\Services\Calls\SchedulerService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Maknz\Slack\Facades\Slack;

//use EnrollmentSMSSender;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        EmailRNDailyReport::class,
        EmailsProvidersToApproveCareplans::class,
        ExportNurseSchedulesToGoogleCalendar::class,
        FormatLocationPhone::class,
        GeneratePatientReports::class,
        ImportNurseScheduleFromGoogleCalendar::class,
        Inspire::class,
        MapSnomedToCpmProblems::class,
        NukeItemAndMeta::class,
        GetAppointments::class,
        GetCcds::class,
        ResetCcmTime::class,
        RecalculateCcmTime::class,
        SplitMergedCcdas::class,
        QueueCcdasToConvertToJson::class,
        QueueCcdaToDetermineEnrollmentEligibility::class,
        QueueCcdasToProcess::class,
        QueueSendAuditReports::class,
        ProcessCcdaLGHMixup::class,
        ImportLGHInsurance::class,
        CheckEmrDirectInbox::class,
        EmailWeeklyReports::class,
    ];

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

            if (!empty($handled)) {
                Slack::to('#background-tasks')->send("The CPMbot just rescheduled some calls");
            }

            foreach ($handled as $call) {
                Slack::to('#background-tasks')->send("We just fixed call: {$call->id}");
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

        //Comments out until we find all the bugs
//        $schedule->command('email:weeklyReports --practice --provider')->weeklyOn(1, '10:00');

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

        $schedule->command('lgh:importInsurance')
            ->dailyAt('05:00');

//        $schedule->command('ccda:toJson')
//            ->everyMinute();

//        $schedule->command('ccda:determineEligibility')
//            ->everyMinute();

//        $schedule->command('ccda:process')
//            ->everyMinute();

        //every 2 hours
//        $schedule->command('ccdas:split-merged')
//            ->cron('0 */2 * * *');

        $schedule->command('send:audit-reports')
            ->monthlyOn(1, '02:00');

        $schedule->command('dm:check')->everyMinute();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
