<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console;

use App\Console\Commands\AssignUnassignedPatientsToStandByNurse;
use App\Console\Commands\AutoApproveValidCarePlansAs;
use App\Console\Commands\CareplanEnrollmentAdminNotification;
use App\Console\Commands\CheckEmrDirectInbox;
use App\Console\Commands\CheckEnrolledPatientsForScheduledCalls;
use App\Console\Commands\CheckForDraftCarePlans;
use App\Console\Commands\CheckForDraftNotesAndQAApproved;
use App\Console\Commands\CheckForMissingLogoutsAndInsert;
use App\Console\Commands\CheckForYesterdaysActivitiesAndUpdateContactWindows;
use App\Console\Commands\CheckUserTotalTimeTracked;
use App\Console\Commands\CountPatientMonthlySummaryCalls;
use App\Console\Commands\CreateApprovableBillablePatientsReport;
use App\Console\Commands\EmailRNDailyReport;
use App\Console\Commands\EmailWeeklyReports;
use App\Console\Commands\EnrollmentFinalAction;
use App\Console\Commands\FaxAuditReportsAtPracticePreferredDayTime;
use App\Console\Commands\GenerateReportForScheduledPAM;
use App\Console\Commands\NursesPerformanceDailyReport;
use App\Console\Commands\OverwriteNBIImportedData;
use App\Console\Commands\OverwriteNBIPatientMRN;
use App\Console\Commands\QueueGenerateNurseDailyReport;
use App\Console\Commands\QueueGenerateOpsDailyReport;
use App\Console\Commands\QueueSendApprovedCareplanSlackNotification;
use App\Console\Commands\QueueSendAuditReports;
use App\Console\Commands\RemoveDuplicateScheduledCalls;
use App\Console\Commands\RemoveScheduledCallsForWithdrawnAndPausedPatients;
use App\Console\Commands\RescheduleMissedCalls;
use App\Console\Commands\ResetPatients;
use App\Console\Commands\SendCarePlanApprovalReminders;
use App\Console\Commands\SendSelfEnrollmentReminders;
use App\Console\Commands\TuneScheduledCalls;
use App\Notifications\NurseDailyReport;
use CircleLinkHealth\Core\Console\Commands\RunScheduler;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Eligibility\Console\Athena\GetAppointmentsForTomorrowFromAthena;
use CircleLinkHealth\Eligibility\Console\Athena\GetCcds;
use CircleLinkHealth\Eligibility\Console\ProcessNextEligibilityBatchChunk;
use CircleLinkHealth\NurseInvoices\Console\Commands\GenerateMonthlyInvoicesForNonDemoNurses;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendMonthlyNurseInvoiceLAN;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendResolveInvoiceDisputeReminder;
use CircleLinkHealth\NurseInvoices\Console\SendMonthlyNurseInvoiceFAN;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * @var array
     */
    protected $commands = [
        RunScheduler::class,
    ];

    /**
     * Register the Closure based commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(CheckEmrDirectInbox::class)
            ->everyTwoMinutes();

        $schedule->command(FaxAuditReportsAtPracticePreferredDayTime::class)
            ->onOneServer()
            ->everyFiveMinutes();

        $schedule->command(AutoApproveValidCarePlansAs::class, ['--reimport'])
            ->everyFiveMinutes()
            ->between('8:00', '23:00');

        $schedule->command(AssignUnassignedPatientsToStandByNurse::class)->twiceDaily(8, 14);
        $schedule->command(RemoveDuplicateScheduledCalls::class)
            ->everyTenMinutes()
            ->between('8:00', '19:00');

        $schedule->command(SendSelfEnrollmentReminders::class, ['--enrollees'])->dailyAt('10:27');
        $schedule->command(EnrollmentFinalAction::class)->dailyAt('08:27');

        $schedule->command('horizon:snapshot')->everyFifteenMinutes();

        $schedule->command(ProcessNextEligibilityBatchChunk::class)
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule->command(RescheduleMissedCalls::class)->everyFifteenMinutes()->onOneServer();

        $schedule->command(CheckEnrolledPatientsForScheduledCalls::class)->dailyAt('00:10')->onOneServer();

        $schedule->command(TuneScheduledCalls::class)->dailyAt('00:15')->onOneServer();

        //family calls will be scheduled in RescheduleMissedCalls
        //$schedule->command(SyncFamilialCalls::class)->dailyAt('00:30')->onOneServer();

        $schedule->command(RemoveScheduledCallsForWithdrawnAndPausedPatients::class)->everyFifteenMinutes()->onOneServer();

        $schedule->command(SendCarePlanApprovalReminders::class)
            ->weekdays()
            ->at('08:00')->onOneServer();

        $schedule->command(EmailWeeklyReports::class, ['--practice', '--provider'])
            ->weeklyOn(1, '10:00')->onOneServer();

        $schedule->command(GetAppointmentsForTomorrowFromAthena::class)
            ->dailyAt('22:30')->onOneServer();

        $schedule->command(GetCcds::class)
            ->dailyAt('03:00')->onOneServer();

        $schedule->command(EmailRNDailyReport::class)
            ->dailyAt('07:05')->onOneServer()->after(function () {
                if ( ! DatabaseNotification::where('type', NurseDailyReport::class)->where(
                    'created_at',
                    '>=',
                    now()->setTime(7, 4)
                )->exists()) {
                    Artisan::queue(EmailRNDailyReport::class);
                }
            });

        $schedule->command(QueueSendApprovedCareplanSlackNotification::class)
            ->dailyAt('23:40')->onOneServer();

        $schedule->command(QueueGenerateOpsDailyReport::class)
            ->dailyAt('23:30')->onOneServer();

        //Run at 12:01am every 1st of month
        $schedule->command(ResetPatients::class)
            ->cron('1 0 1 * *')->onOneServer();

        //Run at 12:45am every 1st of month
        $schedule->command(
            CreateApprovableBillablePatientsReport::class,
            ['--reset-actor', '--auto-attest', now()->subMonth()->startOfMonth()->toDateString()]
        )
            ->cron('45 0 1 * *')->onOneServer();

        $schedule->command(
            CreateApprovableBillablePatientsReport::class,
            ['--reset-actor', now()->startOfMonth()->toDateString()]
        )
            ->dailyAt('23:00');

        $schedule->command(CountPatientMonthlySummaryCalls::class, [now()->startOfMonth()->toDateString()])
            ->dailyAt('21:00');

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
            ->onOneServer();

        $schedule->command(QueueSendAuditReports::class)
            ->monthlyOn(1, '08:00')->onOneServer();

        //uncomment when ready
//        $schedule->command(DownloadTwilioRecordings::class)
//                 ->everyThirtyMinutes()
//                 ->withoutOverlapping()->onOneServer();

        $schedule->command(
            NursesPerformanceDailyReport::class,
            [now()->yesterday()->startOfDay()->toDateString(), '--notify']
        )->dailyAt('00:55')->onOneServer(); // Dont really know if that will help the prob. #CPM-2303

        $schedule->command(CheckForMissingLogoutsAndInsert::class)->dailyAt('04:00');

        $schedule->command(OverwriteNBIImportedData::class)->hourly();

        $schedule->command(OverwriteNBIPatientMRN::class)->everyThirtyMinutes();

        $schedule->command(GenerateMonthlyInvoicesForNonDemoNurses::class)->dailyAt('00:10')->onOneServer();
        $schedule->command(SendMonthlyNurseInvoiceFAN::class)->monthlyOn(1, '08:30')->onOneServer();
        $schedule->command(SendMonthlyNurseInvoiceLAN::class)->everyMinute()->when(function () {
            return SendMonthlyNurseInvoiceLAN::shouldSend();
        })->onOneServer();
        $schedule->command(SendResolveInvoiceDisputeReminder::class)->dailyAt('08:35')->skip(function () {
            return SendResolveInvoiceDisputeReminder::shouldSkip();
        })->onOneServer();
        //        $schedule->command(SendCareCoachApprovedMonthlyInvoices::class)->dailyAt('8:30')->onOneServer();
        $schedule->command(CheckForYesterdaysActivitiesAndUpdateContactWindows::class)->dailyAt('00:10')->onOneServer();

        $schedule->command(CheckUserTotalTimeTracked::class)
            ->dailyAt('01:10')
            ->onOneServer();

        $schedule->command(GenerateReportForScheduledPAM::class)->monthlyOn(date('t'), '23:30');

        $schedule->command(CheckForDraftCarePlans::class)->dailyAt('08:00')->onOneServer();
        $schedule->command(CheckForDraftNotesAndQAApproved::class)->dailyAt('08:10')->onOneServer();
    }
}
