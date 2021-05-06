<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console;

use App\Console\Commands\AlertSlackForPatientsWithNoLocation;
use App\Console\Commands\AssignUnassignedPatientsToStandByNurse;
use App\Console\Commands\CareplanEnrollmentAdminNotification;
use App\Console\Commands\CheckEnrolledPatientsForScheduledCalls;
use App\Console\Commands\CheckForDraftCarePlans;
use App\Console\Commands\CheckForDraftNotesAndQAApproved;
use App\Console\Commands\CheckForMissingLogoutsAndInsert;
use App\Console\Commands\CheckForNullPatientActivities;
use App\Console\Commands\CheckForYesterdaysActivitiesAndUpdateContactWindows;
use App\Console\Commands\CheckUserTotalTimeTracked;
use App\Console\Commands\CreateApprovableBillablePatientsReport;
use App\Console\Commands\EmailRNDailyReport;
use App\Console\Commands\EmailWeeklyReports;
use App\Console\Commands\FaxAuditReportsAtPracticePreferredDayTime;
use App\Console\Commands\FixToledoMakeSureProviderMatchesPracticePull;
use App\Console\Commands\NursesPerformanceDailyReport;
use App\Console\Commands\QueueGenerateNurseDailyReport;
use App\Console\Commands\QueueGenerateOpsDailyReport;
use App\Console\Commands\QueueSendApprovedCareplanSlackNotification;
use App\Console\Commands\QueueSendAuditReports;
use App\Console\Commands\RemoveDuplicateScheduledCalls;
use App\Console\Commands\RescheduleMissedCalls;
use App\Console\Commands\ResetPatients;
use App\Console\Commands\SendCarePlanApprovalReminders;
use App\Notifications\NurseDailyReport;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Console\CheckLocationSummariesHaveBeenCreatedCommand;
use CircleLinkHealth\CcmBilling\Console\CheckPatientEndOfMonthCcmStatusLogsExistForMonthCommand;
use CircleLinkHealth\CcmBilling\Console\CheckPatientSummariesHaveBeenCreatedCommand;
use CircleLinkHealth\CcmBilling\Console\GenerateEndOfMonthCcmStatusLogsCommand;
use CircleLinkHealth\CcmBilling\Console\GenerateServiceSummariesForAllPracticeLocationsCommand;
use CircleLinkHealth\CcmBilling\Console\ProcessAllPracticePatientMonthlyServicesCommand;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\CpmAdmin\Console\Commands\CountPatientMonthlySummaryCalls;
use CircleLinkHealth\Customer\Console\Commands\CheckPatientRoles;
use CircleLinkHealth\Customer\Jobs\RemoveScheduledCallsForUnenrolledPatients;
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
        CountPatientMonthlySummaryCalls::class,
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
        $schedule->command('schedule-monitor:sync')
            ->dailyAt('04:56')
            ->doNotMonitor();

        $schedule->command('schedule-monitor:clean')
            ->daily()
            ->doNotMonitor();

        $schedule->command(SendMonthlyNurseInvoiceLAN::class)
            ->everyMinute()
            ->when(function () {
                return SendMonthlyNurseInvoiceLAN::shouldSend();
            })
            ->doNotMonitor();

        $schedule->command(CheckPatientRoles::class)
            ->everyTwoHours()
            ->doNotMonitor();

        $schedule->command(RemoveDuplicateScheduledCalls::class)
            ->everyFifteenMinutes();

        $schedule->command(FaxAuditReportsAtPracticePreferredDayTime::class)
            ->everyFiveMinutes();

        $schedule->command(RescheduleMissedCalls::class)
            ->everyFifteenMinutes();

        $schedule->job(RemoveScheduledCallsForUnenrolledPatients::class)
            ->everyFifteenMinutes()
            ->monitorName('RemoveScheduledCallsForUnenrolledPatients');

        $schedule->command(AssignUnassignedPatientsToStandByNurse::class)
            ->twiceDaily(8, 14);

        $schedule->command(FixToledoMakeSureProviderMatchesPracticePull::class)
            ->twiceDaily(7, 18);

        //Run at 12:01am every 1st of month
        $schedule->command(ResetPatients::class)
            ->cron('1 0 1 * *');

        $schedule->command(CheckEnrolledPatientsForScheduledCalls::class)
            ->dailyAt('00:10');

        $schedule->command(CheckForYesterdaysActivitiesAndUpdateContactWindows::class)
            ->dailyAt('00:10');

        $schedule->command(GenerateMonthlyInvoicesForNonDemoNurses::class)
            ->dailyAt('00:10');

        //Run at 12:45am every 1st of month
        $schedule->command(
            CreateApprovableBillablePatientsReport::class,
            ['--reset-actor', '--auto-attest', now()->subMonth()->startOfMonth()->toDateString()]
        )
            ->cron('45 0 1 * *');

        $schedule->command(
            NursesPerformanceDailyReport::class,
            [now()->yesterday()->startOfDay()->toDateString(), '--notify']
        )->dailyAt('00:55');

        $schedule->command(CheckUserTotalTimeTracked::class)
            ->dailyAt('01:10');

        $schedule->command(CheckLocationSummariesHaveBeenCreatedCommand::class, [
            Carbon::now()->startOfMonth()->toDateString(),
        ])
            ->monthlyOn(1, '02:20')
            ->monitorName('CheckLocationSummariesHaveBeenCreated');

        $schedule->command(CheckPatientSummariesHaveBeenCreatedCommand::class, [
            Carbon::now()->startOfMonth()->toDateString(),
        ])
            ->monthlyOn(1, '02:30')
            ->monitorName('CheckPatientSummariesHaveBeenCreated');

        $schedule->command(CheckPatientEndOfMonthCcmStatusLogsExistForMonthCommand::class, [
            Carbon::now()->subMonth()->startOfMonth()->toDateString(),
        ])
            ->monthlyOn(1, '02:45')
            ->monitorName('CheckPatientEndOfMonthCcmStatusLogsExistForMonth');

        $schedule->command(CheckForMissingLogoutsAndInsert::class)
            ->dailyAt('04:00');

        $schedule->command(AlertSlackForPatientsWithNoLocation::class)
            ->dailyAt('04:30');

        $schedule->command(CareplanEnrollmentAdminNotification::class)
            ->dailyAt('07:00');

        $schedule->command(EmailRNDailyReport::class)
            ->dailyAt('07:05')
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
            ->at('08:00');

        $schedule->command(QueueSendAuditReports::class)
            ->monthlyOn(1, '08:00');

        $schedule->command(CheckForDraftCarePlans::class)
            ->dailyAt('08:00');

        $schedule->command(CheckForDraftNotesAndQAApproved::class)
            ->dailyAt('08:10');

        $schedule->command(SendMonthlyNurseInvoiceFAN::class)
            ->monthlyOn(1, '08:30');

        $schedule->command(SendResolveInvoiceDisputeReminder::class)
            ->dailyAt('08:35')
            ->skip(function () {
                return SendResolveInvoiceDisputeReminder::shouldSkip();
            })
            ->doNotMonitor();

        $schedule->command(EmailWeeklyReports::class, ['--practice', '--provider'])
            ->weeklyOn(1, '10:00');

        $schedule->command(CheckForNullPatientActivities::class)
            ->days([Schedule::MONDAY, Schedule::WEDNESDAY, Schedule::FRIDAY])
            ->at('11:00');

        $schedule->command(ProcessAllPracticePatientMonthlyServicesCommand::class, [Carbon::now()->startOfMonth()->toDateString()])
            ->monthlyOn(date('t'), '22:00')
            ->monitorName('ProcessAllPracticePatientMonthlyServices');

        $schedule->command(GenerateServiceSummariesForAllPracticeLocationsCommand::class, [Carbon::now()->addMonth()->startOfMonth()->toDateString()])
            ->monthlyOn(date('t'), '22:10')
            ->monitorName('GenerateServiceSummariesForAllPracticeLocations');

        $schedule->command(ProcessAllPracticePatientMonthlyServicesCommand::class, [Carbon::now()->addMonth()->startOfMonth()->toDateString()])
            ->monthlyOn(date('t'), '22:20')
            ->monitorName('ProcessAllPracticePatientMonthlyServices');

        $schedule->command(
            CreateApprovableBillablePatientsReport::class,
            ['--reset-actor', now()->startOfMonth()->toDateString()]
        )
            ->twiceDaily(12, 16);

        $schedule->command(CountPatientMonthlySummaryCalls::class, [now()->startOfMonth()->toDateString()])
            ->twiceDaily(6, 21);

        $schedule->command(QueueGenerateOpsDailyReport::class)
            ->dailyAt('23:30');

        $schedule->command(QueueSendApprovedCareplanSlackNotification::class)
            ->dailyAt('23:40');

        $schedule->command(QueueGenerateNurseDailyReport::class)
            ->dailyAt('23:45')
            ->withoutOverlapping();

        $schedule->command(GenerateEndOfMonthCcmStatusLogsCommand::class, [now()->startOfMonth()->toDateString()])
            ->monthlyOn(date('t'), '23:50')
            ->monitorName('GenerateEndOfMonthCcmStatusLogs');
    }
}
