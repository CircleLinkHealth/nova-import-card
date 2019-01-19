<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console;

use App\Console\Commands\Athena\AutoPullEnrolleesFromAthena;
use App\Console\Commands\Athena\DetermineTargetPatientEligibility;
use App\Console\Commands\Athena\GetAppointments;
use App\Console\Commands\Athena\GetCcds;
use App\Console\Commands\AttachBillableProblemsToLastMonthSummary;
use App\Console\Commands\CareplanEnrollmentAdminNotification;
use App\Console\Commands\CheckEmrDirectInbox;
use App\Console\Commands\DeleteProcessedFiles;
use App\Console\Commands\DownloadTwilioRecordings;
use App\Console\Commands\EmailRNDailyReport;
use App\Console\Commands\EmailWeeklyReports;
use App\Console\Commands\NursesAndStatesDailyReport;
use App\Console\Commands\QueueEligibilityBatchForProcessing;
use App\Console\Commands\QueueGenerateNurseDailyReport;
use App\Console\Commands\QueueGenerateNurseInvoices;
use App\Console\Commands\QueueGenerateOpsDailyReport;
use App\Console\Commands\QueueResetAssignedCareAmbassadorsFromEnrollees;
use App\Console\Commands\QueueSendApprovedCareplanSlackNotification;
use App\Console\Commands\QueueSendAuditReports;
use App\Console\Commands\RemoveScheduledCallsForWithdrawnAndPausedPatients;
use App\Console\Commands\RescheduleMissedCalls;
use App\Console\Commands\ResetPatients;
use App\Console\Commands\TuneScheduledCalls;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Jorijn\LaravelSecurityChecker\Console\SecurityMailCommand;

class Kernel extends ConsoleKernel
{
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
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        
        $schedule->command(DetermineTargetPatientEligibility::class)
                 ->everyTenMinutes();
        
        $schedule->command(QueueEligibilityBatchForProcessing::class)
                 ->everyMinute()
                 ->withoutOverlapping();
        
        $schedule->command(AutoPullEnrolleesFromAthena::class)
                 ->monthlyOn(1);
        
        $schedule->command(RescheduleMissedCalls::class)->dailyAt('00:01');
        
        $schedule->command(TuneScheduledCalls::class)->dailyAt('00:05');

//        $schedule->call(function () {
//            (new EnrollmentSMSSender())->exec();
//        })->dailyAt('13:00');
        
        //family calls will be scheduled in RescheduleMissedCalls
        //$schedule->command(SyncFamilialCalls::class)->dailyAt('00:30');
        
        //Removes All Scheduled Calls for patients that are withdrawn
        $schedule->command(RemoveScheduledCallsForWithdrawnAndPausedPatients::class)->everyFiveMinutes(
        )->withoutOverlapping();
        
        $schedule->command(EmailWeeklyReports::class, ['--practice', '--provider'])
                 ->weeklyOn(1, '10:00');
        
        $schedule->command('emailapprovalreminder:providers')
                 ->weekdays()
                 ->at('08:00');
        
        //commenting out due to isues with google calendar
//        $schedule->command('nurseSchedule:export')
//                 ->hourly();
        
        $schedule->command(GetAppointments::class)
                 ->dailyAt('22:30');
        
        $schedule->command(QueueResetAssignedCareAmbassadorsFromEnrollees::class)
                 ->dailyAt('00:30');
        
        $schedule->command(GetCcds::class)
                 ->everyThirtyMinutes();
        
        $schedule->command(EmailRNDailyReport::class)
                 ->dailyAt('21:00');
        
        $schedule->command(QueueSendApprovedCareplanSlackNotification::class)
                 ->dailyAt('23:40');
        
        $schedule->command(QueueGenerateOpsDailyReport::class)
                 ->dailyAt('23:30');
        
        //Run at 12:01am every 1st of month
        $schedule->command(ResetPatients::class)
                 ->cron('1 0 1 * *');
        
        //Run at 12:30am every 1st of month
        $schedule->command(AttachBillableProblemsToLastMonthSummary::class)
                 ->cron('30 0 1 * *');

//        $schedule->command('lgh:importInsurance')
//            ->dailyAt('05:00');
        
        $schedule->command(QueueGenerateNurseInvoices::class)
                 ->dailyAt('23:40')
                 ->withoutOverlapping();
        
        $schedule->command(QueueGenerateNurseDailyReport::class)
                 ->dailyAt('23:45')
                 ->withoutOverlapping();
        
        $schedule->command(CareplanEnrollmentAdminNotification::class)
                 ->dailyAt('09:00')
                 ->withoutOverlapping();

//        $schedule->command('ccda:determineEligibility')
//                 ->everyFiveMinutes()
//                 ->withoutOverlapping();

//        $schedule->command('ccda:toJson')
//            ->everyMinute()
//            ->withoutOverlapping();

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
        
        //uncomment when ready
//        $schedule->command(DownloadTwilioRecordings::class)
//                 ->everyThirtyMinutes()
//                 ->withoutOverlapping();

//        Disable backup till we fix the issue of it not running
//        if (app()->environment('worker')) {
//            $schedule->command(CleanupCommand::class)->daily()->at('01:00');
//            $schedule->command(BackupCommand::class)->daily()->at('02:00');
//        }
        
        $schedule->command(SecurityMailCommand::class)
                 ->weekly();
        $schedule->command(NursesAndStatesDailyReport::class)->everyMinute();//->dailyAt('00:10');
    }
}
