<?php

namespace App\Console\Commands;

use App\Practice;
use App\Reports\Sales\Practice\SalesByPracticeReport;
use App\Reports\Sales\Provider\SalesByProviderReport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maknz\Slack\Facades\Slack;

class EmailWeeklyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:weeklyReports {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emails weekly practice reports to distributors and providers. If email is passed to the command, all emails will be sent to that email (ie. useful for testing)';

    protected $activePractices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->activePractices = Practice::active();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $testerEmail = null;

        if ($this->argument('email')) {
            $testerEmail = $this->argument('email');
        }

        $startRange = Carbon::now()->setTime(0, 0, 0)->subWeek();
        $endRange = Carbon::now()->setTime(0, 0, 0);

        foreach ($this->activePractices as $practice) {
            $this->sendPracticeReport($practice, $startRange, $endRange, $testerEmail);
            $this->sendProviderReport($practice, $startRange, $endRange, $testerEmail);
        }
    }

    public function sendProviderReport($practice, $startRange, $endRange, $testerEmail) {
        $providers_for_practice = $practice->getProviders($practice->id);

        //handle providers
        foreach ($providers_for_practice as $provider) {

            $providerData =
                (new SalesByProviderReport(
                    $provider,
                    SalesByProviderReport::SECTIONS,
                    $startRange,
                    $endRange
                ))
                    ->data(true);

            $providerData['name'] = $provider->display_name;
            $providerData['start'] = $startRange->toDateString();
            $providerData['end'] = $endRange->toDateString();
            $providerData['isEmail'] = true;


            $subjectProvider = 'Dr. ' . $provider->last_name . '\'s CCM Weekly Summary';

            if ($testerEmail) {
                Mail::send('sales.by-provider.report', ['data' => $providerData], function ($message) use (
                    $provider,
                    $subjectProvider,
                    $testerEmail
                ) {
                    $message->from('notifications@careplanmanager.com', 'CircleLink Health');
                    $message->to($testerEmail)->subject($subjectProvider);
                });
            } else {
                Mail::send('sales.by-provider.report', ['data' => $providerData], function ($message) use (
                    $provider,
                    $subjectProvider
                ) {
                    $message->from('notifications@careplanmanager.com', 'CircleLink Health');
                    $message->to($provider->email)->subject($subjectProvider);
                });
            }

//                Slack::to('#background-tasks')
//                    ->send("The CPMbot just sent the provider's summary for $practice->display_name to $provider->fullName");

        }
    }

    public function sendPracticeReport(Practice $practice, $startRange, $endRange, $testerEmail) {
        $subjectPractice = $practice->display_name . '\'s CCM Weekly Summary';

        $practiceData = (new SalesByPracticeReport(
            $practice,
            SalesByPracticeReport::SECTIONS,
            $startRange,
            $endRange

        ))->data(true);

        $practiceData['name'] = $practice->display_name;
        $practiceData['start'] = $startRange->toDateString();
        $practiceData['end'] = $endRange->toDateString();
        $practiceData['isEmail'] = true;

        if ($practice->weekly_report_recipients != null) {

            $organizationSummaryRecipients = explode(', ', trim($practice->weekly_report_recipients));

            if ($testerEmail) {
                $organizationSummaryRecipients = [$testerEmail];
            }

            //handle leads
            foreach ($organizationSummaryRecipients as $recipient) {

                Mail::send('sales.by-practice.report', ['data' => $practiceData], function ($message) use (
                    $recipient,
                    $subjectPractice
                ) {
                    $message->from('notifications@careplanmanager.com', 'CircleLink Health');
                    $message->to($recipient)->subject($subjectPractice);
                });

//                    Slack::to('#background-tasks')
//                        ->send("The CPMbot just sent the organization weekly summary for $practice->display_name to $recipient");

            }
        }
    }
}
