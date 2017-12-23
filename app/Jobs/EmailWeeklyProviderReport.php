<?php

namespace App\Jobs;

use App\Notifications\WeeklyProviderReport;
use App\Practice;
use App\Reports\Sales\Provider\SalesByProviderReport;
use App\User;
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
    protected $tester;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Practice $practice, $startRange, $endRange, User $tester = null)
    {
        $this->practice = $practice;
        $this->startRange = $startRange;
        $this->endRange = $endRange;
        $this->tester = $tester;
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

            if ($this->tester) {
                $this->tester->notify(new WeeklyPrLoviderReport($providerData));
            } else {
                $provider->notify(new WeeklyProviderReport($providerData));
            }
        }
    }
}
