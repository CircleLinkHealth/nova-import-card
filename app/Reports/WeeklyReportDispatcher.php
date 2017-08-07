<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 3/15/17
 * Time: 10:21 AM
 */

namespace App\Reports;


use App\Practice;
use App\Reports\Sales\Practice\SalesByPracticeReport;
use App\Reports\Sales\Provider\SalesByProviderReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Maknz\Slack\Facades\Slack;

class WeeklyReportDispatcher
{

    protected $practiceList;

    public function __construct()
    {
        $this->practiceList = Practice::active();
    }

    public function exec(){

        $startRange = Carbon::now()->setTime(0, 0, 0)->subWeek();
        $endRange = Carbon::now()->setTime(0, 0, 0);

        foreach ($this->practiceList as $practice) {
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

            if($practice->weekly_report_recipients != null) {

                $organizationSummaryRecipients = explode(', ', trim($practice->weekly_report_recipients));

                //handle leads
                foreach ($organizationSummaryRecipients as $recipient) {

                    Mail::send('sales.by-practice.report', ['data' => $practiceData], function ($message) use
                    (
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

                Mail::send('sales.by-provider.report', ['data' => $providerData], function ($message) use
                (
                    $provider,
                    $subjectProvider
                ) {
                    $message->from('notifications@careplanmanager.com', 'CircleLink Health');
                    $message->to($provider->email)->subject($subjectProvider);
                });

//                Slack::to('#background-tasks')
//                    ->send("The CPMbot just sent the provider's summary for $practice->display_name to $provider->fullName");

            }

        }
    }

}