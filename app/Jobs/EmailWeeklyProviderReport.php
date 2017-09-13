<?php

namespace App\Jobs;

use App\Practice;
use App\Reports\Sales\Provider\SalesByProviderReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Maknz\Slack\Facades\Slack;

class EmailWeeklyProviderReport implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $practice;
    protected $startRange;
    protected $endRange;
    protected $testerEmail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Practice $practice, $startRange, $endRange, $testerEmail)
    {
        $this->practice = $practice;
        $this->startRange = $startRange;
        $this->endRange = $endRange;
        $this->testerEmail = $testerEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $providers_for_practice = $this->practice->getProviders($this->practice->id);

        //handle providers
        foreach ($providers_for_practice as $provider) {

            $providerData =
                (new SalesByProviderReport(
                    $provider,
                    SalesByProviderReport::SECTIONS,
                    $this->startRange->copy(),
                    $this->endRange->copy()
                ))
                    ->data(true);

            $providerData['name'] = $provider->display_name;
            $providerData['start'] = $this->startRange;
            $providerData['end'] = $this->endRange;
            $providerData['isEmail'] = true;


            $subjectProvider = 'Dr. ' . $provider->last_name . '\'s CCM Weekly Summary';

            if ($this->testerEmail) {
                Mail::send('sales.by-provider.report', ['data' => $providerData], function ($message) use (
                    $provider,
                    $subjectProvider
                ) {
                    $message->from('notifications@careplanmanager.com', 'CircleLink Health');
                    $message->to($this->testerEmail)->subject($subjectProvider);
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
        }
    }
}
