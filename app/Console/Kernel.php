<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console;

use App\Console\Commands\AttachBillableProblemsToLastMonthSummary;
use App\Console\Commands\CareplanEnrollmentAdminNotification;
use App\Console\Commands\CheckEmrDirectInbox;
use App\Console\Commands\CheckEnrolledPatientsForScheduledCalls;
use App\Console\Commands\EmailRNDailyReport;
use App\Console\Commands\EmailWeeklyReports;
use App\Console\Commands\NursesPerformanceDailyReport;
use App\Console\Commands\OverwriteNBIImportedData;
use App\Console\Commands\OverwriteNBIPatientMRN;
use App\Console\Commands\QueueGenerateNurseDailyReport;
use App\Console\Commands\QueueGenerateOpsDailyReport;
use App\Console\Commands\QueueResetAssignedCareAmbassadorsFromEnrollees;
use App\Console\Commands\QueueSendApprovedCareplanSlackNotification;
use App\Console\Commands\QueueSendAuditReports;
use App\Console\Commands\RemoveScheduledCallsForWithdrawnAndPausedPatients;
use App\Console\Commands\RescheduleMissedCalls;
use App\Console\Commands\ResetPatients;
use App\Console\Commands\RunScheduler;
use App\Console\Commands\SendCarePlanApprovalReminders;
use App\Console\Commands\TuneScheduledCalls;
use CircleLinkHealth\Eligibility\Console\Athena\AutoPullEnrolleesFromAthena;
use CircleLinkHealth\Eligibility\Console\Athena\DetermineTargetPatientEligibility;
use CircleLinkHealth\Eligibility\Console\Athena\GetAppointments;
use CircleLinkHealth\Eligibility\Console\Athena\GetCcds;
use CircleLinkHealth\Eligibility\Console\ProcessNextEligibilityBatchChunk;
use CircleLinkHealth\NurseInvoices\Console\Commands\GenerateMonthlyInvoicesForNonDemoNurses;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendMonthlyNurseInvoiceLAN;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendResolveInvoiceDisputeReminder;
use CircleLinkHealth\NurseInvoices\Console\SendMonthlyNurseInvoiceFAN;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Tests\Commands\CreateAndSeedTestSuiteDB;

class Kernel extends ConsoleKernel
{
    /**
     * @var array
     */
    protected $commands = [
        RunScheduler::class,
        CreateAndSeedTestSuiteDB::class,
    ];

    /**
     * Register the Closure based commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        if ('local' == $this->app->environment()) {
            $this->load(__DIR__.'/DevCommands');
        }

        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('horizon:snapshot')->everyFiveMinutes()->onOneServer();

        $schedule->command(DetermineTargetPatientEligibility::class)
            ->dailyAt('04:00')->onOneServer();

        $schedule->command(ProcessNextEligibilityBatchChunk::class)
            ->everyFiveMinutes()
            ->withoutOverlapping()->onOneServer();

        $schedule->command(AutoPullEnrolleesFromAthena::class)
            ->monthlyOn(1)->onOneServer();

        $schedule->command(RescheduleMissedCalls::class)->dailyAt('00:01')->onOneServer();

        $schedule->command(CheckEnrolledPatientsForScheduledCalls::class)->dailyAt('00:10')->onOneServer();

        $schedule->command(TuneScheduledCalls::class)->dailyAt('00:15')->onOneServer();

        //family calls will be scheduled in RescheduleMissedCalls
        //$schedule->command(SyncFamilialCalls::class)->dailyAt('00:30')->onOneServer();

        //Removes All Scheduled Calls for patients that are withdrawn
        $schedule->command(RemoveScheduledCallsForWithdrawnAndPausedPatients::class)->everyFiveMinutes()->withoutOverlapping()->onOneServer();

        $schedule->command(EmailWeeklyReports::class, ['--practice', '--provider'])
            ->weeklyOn(1, '10:00')->onOneServer();

        $schedule->command(SendCarePlanApprovalReminders::class)
            ->weekdays()
            ->at('08:00')->onOneServer();

        $schedule->command(GetAppointments::class)
            ->dailyAt('22:30')->onOneServer();

        $schedule->command(QueueResetAssignedCareAmbassadorsFromEnrollees::class)
            ->dailyAt('00:40')->onOneServer();

        $schedule->command(GetCcds::class)
            ->dailyAt('03:00')->onOneServer();

        $schedule->command(EmailRNDailyReport::class)
            ->dailyAt('07:05')->onOneServer();

        $schedule->command(QueueSendApprovedCareplanSlackNotification::class)
            ->dailyAt('23:40')->onOneServer();

        $schedule->command(QueueGenerateOpsDailyReport::class)
            ->dailyAt('23:30')->onOneServer();

        //Run at 12:01am every 1st of month
        $schedule->command(ResetPatients::class)
            ->cron('1 0 1 * *')->onOneServer();

        //Run at 12:45am every 1st of month
        $schedule->command(AttachBillableProblemsToLastMonthSummary::class, ['--reset-actor' => true])
            ->cron('45 0 1 * *')->onOneServer();

        $schedule->command(AttachBillableProblemsToLastMonthSummary::class)
            ->dailyAt('00:25')->onOneServer();

//        $schedule->command(
//            SendCareCoachInvoices::class,
//            [
//                '--variable-time' => true,
//            ]
//        )->monthlyOn(1, '5:0')->onOneServer();

        $schedule->command(QueueGenerateNurseDailyReport::class)
            ->dailyAt('23:45')
            ->withoutOverlapping()->onOneServer();

        $schedule->command(CareplanEnrollmentAdminNotification::class)
            ->dailyAt('07:00')
            ->withoutOverlapping()->onOneServer();

//        $schedule->command('ccda:determineEligibility')
//                 ->everyFiveMinutes()
//                 ->withoutOverlapping()->onOneServer();

//        $schedule->command('ccda:toJson')
//            ->everyMinute()
//            ->withoutOverlapping()->onOneServer();

//        $schedule->command('ccda:process')
//            ->everyMinute()
//            ->withoutOverlapping()->onOneServer();

        //every 2 hours
//        $schedule->command('ccdas:split-merged')
//            ->cron('0 */2 * * *')->onOneServer();

        $schedule->command(QueueSendAuditReports::class)
            ->monthlyOn(1, '02:00')->onOneServer();

        $schedule->command(CheckEmrDirectInbox::class)
            ->everyFiveMinutes()
            ->withoutOverlapping()->onOneServer();

        //uncomment when ready
//        $schedule->command(DownloadTwilioRecordings::class)
//                 ->everyThirtyMinutes()
//                 ->withoutOverlapping()->onOneServer();

        $schedule->command(NursesPerformanceDailyReport::class)->dailyAt('00:03')->onOneServer();

        $schedule->command(OverwriteNBIImportedData::class)->everyThirtyMinutes()->onOneServer();

        $schedule->command(OverwriteNBIPatientMRN::class)->everyThirtyMinutes()->onOneServer();

        $schedule->command(GenerateMonthlyInvoicesForNonDemoNurses::class)->dailyAt('00:10')->onOneServer();
        $schedule->command(SendMonthlyNurseInvoiceFAN::class)->monthlyOn(1, '08:30')->onOneServer();

        $schedule->command(SendMonthlyNurseInvoiceLAN::class)->everyMinute()->when(function () {
            return SendMonthlyNurseInvoiceLAN::shouldSend();
        })->onOneServer();

        $schedule->command(SendResolveInvoiceDisputeReminder::class)->dailyAt('08:35')->skip(function () {
            return SendResolveInvoiceDisputeReminder::shouldSkip();
        })->onOneServer();
        //        $schedule->command(SendCareCoachApprovedMonthlyInvoices::class)->dailyAt('8:30')->onOneServer();
    }
}
