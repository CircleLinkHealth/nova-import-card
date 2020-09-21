<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console;

use App\Console\Commands\AssignUnassignedPatientsToStandByNurse;
use App\Console\Commands\CareplanEnrollmentAdminNotification;
use App\Console\Commands\CheckEmrDirectInbox;
use App\Console\Commands\CheckEnrolledPatientsForScheduledCalls;
use App\Console\Commands\CheckForDraftCarePlans;
use App\Console\Commands\CheckForDraftNotesAndQAApproved;
use App\Console\Commands\CheckForMissingLogoutsAndInsert;
use App\Console\Commands\CheckForYesterdaysActivitiesAndUpdateContactWindows;
use App\Console\Commands\CheckUserTotalTimeTracked;
use App\Console\Commands\CheckVoiceCalls;
use App\Console\Commands\CountPatientMonthlySummaryCalls;
use App\Console\Commands\CreateApprovableBillablePatientsReport;
use App\Console\Commands\EmailRNDailyReport;
use App\Console\Commands\EmailWeeklyReports;
use App\Console\Commands\EnrollmentFinalAction;
use App\Console\Commands\FaxAuditReportsAtPracticePreferredDayTime;
use App\Console\Commands\FixToledoMakeSureProviderMatchesPracticePull;
use App\Console\Commands\GenerateReportForScheduledPAM;
use App\Console\Commands\NursesPerformanceDailyReport;
use App\Console\Commands\QueueGenerateNurseDailyReport;
use App\Console\Commands\QueueGenerateOpsDailyReport;
use App\Console\Commands\QueueSendApprovedCareplanSlackNotification;
use App\Console\Commands\QueueSendAuditReports;
use App\Console\Commands\RemoveDuplicateScheduledCalls;
use App\Console\Commands\RescheduleMissedCalls;
use App\Console\Commands\ResetPatients;
use App\Console\Commands\SendCarePlanApprovalReminders;
use App\Console\Commands\SendSelfEnrollmentReminders;
use App\Console\Commands\SendUnsuccessfulCallPatientsReminderNotification;
use App\Jobs\OverwritePatientMrnsFromSupplementalData;
use App\Jobs\RemoveScheduledCallsForUnenrolledPatients;
use App\Notifications\NurseDailyReport;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\CheckPatientEndOfMonthCcmStatusLogsExistForMonth;
use CircleLinkHealth\CcmBilling\Jobs\CheckPatientSummariesHaveBeenCreated;
use CircleLinkHealth\CcmBilling\Jobs\GenerateEndOfMonthCcmStatusLogs;
use CircleLinkHealth\CcmBilling\Jobs\GenerateServiceSummariesForAllPracticeLocations;
use CircleLinkHealth\CcmBilling\Jobs\ProcessAllPracticePatientMonthlyServices;
use CircleLinkHealth\Core\Console\Commands\RunScheduler;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\AutoCarePlanQAApproval\ConsentedEnrollees as ImportAndAutoQAApproveConsentedEnrollees;
use CircleLinkHealth\Eligibility\AutoCarePlanQAApproval\Patients as AutoQAApproveValidPatients;
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
use Laravel\Scout\Console\ImportCommand;

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
     * NOTE:
     * Try to order the commands by time, in ascending order. i.e.:
     * 09:00
     * 09:10
     * 10:15
     * ...
     * ...
     * 23:55
     * 23:59.
     */
    protected function schedule(Schedule $schedule)
    {
        ini_set('max_execution_time', 900);
        ini_set('memory_limit', '800M');

        $schedule->command(SendMonthlyNurseInvoiceLAN::class)
            ->everyMinute()
            ->when(function () {
                return SendMonthlyNurseInvoiceLAN::shouldSend();
            })
            ->onOneServer();

        $schedule->command(CheckEmrDirectInbox::class)
            ->everyFiveMinutes();

        $schedule->command(RemoveDuplicateScheduledCalls::class)
            ->everyFifteenMinutes();

        $schedule->command(FaxAuditReportsAtPracticePreferredDayTime::class)
            ->onOneServer()
            ->everyFiveMinutes();

        $schedule->job(AutoQAApproveValidPatients::class)
            ->everyFifteenMinutes()
            ->between('8:00', '23:00');

        $schedule->job(ImportAndAutoQAApproveConsentedEnrollees::class)
            ->everyFifteenMinutes()
            ->between('8:00', '23:00');

        $schedule->command(ProcessNextEligibilityBatchChunk::class)
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        $schedule->command(RescheduleMissedCalls::class)
            ->everyFifteenMinutes()
            ->onOneServer();

        $schedule->job(RemoveScheduledCallsForUnenrolledPatients::class)
            ->everyFifteenMinutes()
            ->onOneServer();

        $schedule->command('horizon:snapshot')
            ->everyThirtyMinutes();

        $schedule->job(OverwritePatientMrnsFromSupplementalData::class)
            ->everyThirtyMinutes();

        $schedule->command(CheckVoiceCalls::class, [now()->subHour()])
            ->hourly()
            ->between('7:00', '23:00');

        $schedule->command('schedule-monitor:clean')
            ->daily();

        $schedule->command(AssignUnassignedPatientsToStandByNurse::class)
            ->twiceDaily(8, 14);

        $schedule->command(FixToledoMakeSureProviderMatchesPracticePull::class)
            ->twiceDaily(7, 18);

        //Run at 12:01am every 1st of month
        $schedule->command(ResetPatients::class)
            ->cron('1 0 1 * *')
            ->onOneServer();

        $schedule->command(CheckEnrolledPatientsForScheduledCalls::class)
            ->dailyAt('00:10')
            ->onOneServer();

        $schedule->command(CheckForYesterdaysActivitiesAndUpdateContactWindows::class)
            ->dailyAt('00:10')
            ->onOneServer();

        $schedule->command(GenerateMonthlyInvoicesForNonDemoNurses::class)
            ->dailyAt('00:10')
            ->onOneServer();

        //Run at 12:45am every 1st of month
        $schedule->command(
            CreateApprovableBillablePatientsReport::class,
            ['--reset-actor', '--auto-attest', now()->subMonth()->startOfMonth()->toDateString()]
        )
            ->cron('45 0 1 * *')
            ->onOneServer();

        $schedule->command(
            NursesPerformanceDailyReport::class,
            [now()->yesterday()->startOfDay()->toDateString(), '--notify']
        )->dailyAt('00:55')
            ->onOneServer();

        $schedule->command(CheckUserTotalTimeTracked::class)
            ->dailyAt('01:10')
            ->onOneServer();

        $schedule->command(GetCcds::class)
            ->dailyAt('03:00')
            ->onOneServer();

        $schedule->command(ImportCommand::class, [
            User::class,
        ])->dailyAt('03:05');

        $schedule->command(ImportCommand::class, [
            Practice::class,
        ])->dailyAt('03:10');

        $schedule->command(ImportCommand::class, [
            Location::class,
        ])->dailyAt('03:15');

        $schedule->job(CheckPatientSummariesHaveBeenCreated::class, [
            Carbon::now()->subMonth()->startOfMonth()->toDateString(),
        ])
            ->monthlyOn(1, '03:30');

        $schedule->job(CheckPatientEndOfMonthCcmStatusLogsExistForMonth::class, [
            Carbon::now()->subMonth()->startOfMonth()->toDateString(),
        ])
            ->monthlyOn(1, '03:45');

        $schedule->command(CheckForMissingLogoutsAndInsert::class)
            ->dailyAt('04:00');

        $schedule->command(CareplanEnrollmentAdminNotification::class)
            ->dailyAt('07:00')
            ->onOneServer();

        $schedule->command(EmailRNDailyReport::class)
            ->dailyAt('07:05')
            ->onOneServer()
            ->after(function () {
                if ( ! DatabaseNotification::where('type', NurseDailyReport::class)->where(
                    'created_at',
                    '>=',
                    now()->setTime(7, 4)
                )->exists()) {
                    Artisan::queue(EmailRNDailyReport::class);
                }
            });

        $schedule->command(SendCarePlanApprovalReminders::class)
            ->weekdays()
            ->at('08:00')
            ->onOneServer();

        $schedule->command(QueueSendAuditReports::class)
            ->monthlyOn(1, '08:00')
            ->onOneServer();

        $schedule->command(CheckForDraftCarePlans::class)
            ->dailyAt('08:00')
            ->onOneServer();

        $schedule->command(CheckForDraftNotesAndQAApproved::class)
            ->dailyAt('08:10')
            ->onOneServer();

        $schedule->command(EnrollmentFinalAction::class)
            ->dailyAt('08:27');

        $schedule->command(SendMonthlyNurseInvoiceFAN::class)
            ->monthlyOn(1, '08:30')
            ->onOneServer();

        $schedule->command(SendResolveInvoiceDisputeReminder::class)
            ->dailyAt('08:35')
            ->skip(function () {
                return SendResolveInvoiceDisputeReminder::shouldSkip();
            })
            ->onOneServer();

        $schedule->command(SendUnsuccessfulCallPatientsReminderNotification::class)
            ->dailyAt('10:00')
            ->onOneServer();

        $schedule->command(EmailWeeklyReports::class, ['--practice', '--provider'])
            ->weeklyOn(1, '10:00')
            ->onOneServer();

        $schedule->command(SendSelfEnrollmentReminders::class, ['--enrollees'])
            ->dailyAt('10:27');

        $schedule->job(GenerateServiceSummariesForAllPracticeLocations::class, [Carbon::now()->addMonth()->startOfMonth()->toDateString()])
            ->monthlyOn(date('t'), '22:00')
            ->onOneServer();

        $schedule->job(ProcessAllPracticePatientMonthlyServices::class, [Carbon::now()->addMonth()->startOfMonth()->toDateString()])
            ->monthlyOn(date('t'), '22:10')
            ->onOneServer();

        $schedule->command(GetAppointmentsForTomorrowFromAthena::class)
            ->dailyAt('22:30')
            ->onOneServer();

        $schedule->command(
            CreateApprovableBillablePatientsReport::class,
            ['--reset-actor', now()->startOfMonth()->toDateString()]
        )
            ->twiceDaily(12, 16);

        $schedule->command(CountPatientMonthlySummaryCalls::class, [now()->startOfMonth()->toDateString()])
            ->twiceDaily(6, 21);

        $schedule->command(QueueGenerateOpsDailyReport::class)
            ->dailyAt('23:30')
            ->onOneServer();

        $schedule->command(GenerateReportForScheduledPAM::class)
            ->monthlyOn(date('t'), '23:30');

        $schedule->command(QueueSendApprovedCareplanSlackNotification::class)
            ->dailyAt('23:40')
            ->onOneServer();

        $schedule->command(QueueGenerateNurseDailyReport::class)
            ->dailyAt('23:45')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->job(GenerateEndOfMonthCcmStatusLogs::class, [now()->startOfMonth()->toDateString()])
            ->monthlyOn(date('t'), '23:50')
            ->onOneServer();
    }
}
