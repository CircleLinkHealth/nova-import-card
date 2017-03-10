<?php namespace App\Console;

use App\Algorithms\Calls\PredictCall;
use App\Algorithms\Calls\ReschedulerHandler;
use App\Console\Commands\Athena\GetAppointments;
use App\Console\Commands\Athena\GetCcds;
use App\Console\Commands\EmailRNDailyReport;
use App\Console\Commands\EmailsProvidersToApproveCareplans;
use App\Console\Commands\ExportNurseSchedulesToGoogleCalendar;
use App\Console\Commands\FormatLocationPhone;
use App\Console\Commands\GeneratePatientReports;
use App\Console\Commands\ImportNurseScheduleFromGoogleCalendar;
use App\Console\Commands\Inspire;
use App\Console\Commands\MapSnomedToCpmProblems;
use App\Console\Commands\NukeItemAndMeta;
use App\Console\Commands\RecalculateCcmTime;
use App\Console\Commands\ResetCcmTime;
use App\MailLog;
use App\Practice;
use App\Reports\Sales\Practice\SalesByPracticeReport;
use App\Reports\Sales\Provider\SalesByProviderReport;
use App\Services\Calls\SchedulerService;
use App\Services\PhiMail\PhiMail;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Mail;
use Maknz\Slack\Facades\Slack;


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
        $schedule->call(function () {
            (new PhiMail)->receive();
        })->everyMinute();

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

        //syncs families.
        $schedule->call(function () {
            (new SchedulerService())->syncFamilialCalls();
        })->dailyAt('00:30');

        //Removes All Scheduled Calls for patients that are withdrawn
        $schedule->call(function () {
            (new SchedulerService())->removeScheduledCallsForWithdrawnAndPausedPatients();
        })->everyMinute();

        $schedule->call(function () {

            $practicesToSendTo = [
                'carolina-medical-associates',
                'clinicalosangeles',
                'elmwood',
                'tabernacle',
                'envision',
                'mazhar',
                'middletownmedical',
                'montgomery',
                'nestor',
                'rocky-mountain-health-centers-south',
                'upg',
                'urgent-medical-care-pc',
                'quest-medical-care-pc',
                'premier-heart-and-vein-care',
                'river-city'
            ];

            //@todo check range
            $startRange = Carbon::now()->setTime(0, 0, 0)->subWeek();
            $endRange = Carbon::now()->setTime(0, 0, 0);

            foreach ($practicesToSendTo as $name) {

                $practice = Practice::whereName($name)->first();

                $subjectPractice = $practice->display_name . 's Organization Weekly Summary';

                $practiceData = (new SalesByPracticeReport(
                    $practice,
                    SalesByPracticeReport::SECTIONS,
                    $startRange,
                    $endRange

                ))->data(true);

                $practiceData['name'] = $practice->name;
                $practiceData['start'] = $startRange->toDateString();
                $practiceData['end'] = $endRange->toDateString();

                $organizationSummaryRecipiets = explode(', ', trim($practice->weekly_report_recipients));

                //handle leads
                foreach ($organizationSummaryRecipiets as $recipient) {

                    Mail::send('sales.by-practice.report', ['data' => $practiceData], function ($message) use
                    (
                        $recipient,
                        $subjectPractice
                    ) {
                        $message->from('notifications@careplanmanager.com', 'CircleLink Health');
                        $message->to($recipient)->subject($subjectPractice);
                    });

                    echo $recipient;

                }

                $providers_for_practice = $practice->getProviders($practice->id);

                //handle providers
                foreach ($providers_for_practice as $provider) {

                    $providerData = (new SalesByProviderReport(
                        $provider,
                        SalesByProviderReport::SECTIONS,
                        $startRange,
                        $endRange
                    ))->data(true);

                    $providerData['name'] = $provider->display_name;
                    $providerData['start'] = $startRange->toDateString();
                    $providerData['end'] = $endRange->toDateString();

                    $subjectProvider = 'Dr. ' . $provider->last_name . '\'s Patient Weekly Summary';

                    Mail::send('sales.by-provider.report', ['data' => $providerData], function ($message) use
                    (
                        $provider,
                        $subjectProvider
                    ) {
                        $message->from('notifications@careplanmanager.com', 'CircleLink Health');
                        $message->to($provider->email)->subject($subjectProvider);
                    });

                }
            }

        }

        )->weeklyOn(1, '10:00');

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
