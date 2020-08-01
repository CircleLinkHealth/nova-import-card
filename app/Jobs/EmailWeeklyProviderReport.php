<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\WeeklyProviderReport;
use App\Reports\Sales\Provider\SalesByProviderReport;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EmailWeeklyProviderReport implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected $endRange;

    protected $practice;
    protected $startRange;
    protected $tester;

    /**
     * Create a new job instance.
     *
     * @param mixed $startRange
     * @param mixed $endRange
     */
    public function __construct(Practice $practice, $startRange, $endRange, User $tester = null)
    {
        $this->practice   = $practice;
        $this->startRange = $startRange;
        $this->endRange   = $endRange;
        $this->tester     = $tester;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $providers_for_practice = Practice::getProviders($this->practice->id);

        //handle providers
        foreach ($providers_for_practice as $provider) {
            $providerData = (new SalesByProviderReport(
                $provider,
                SalesByProviderReport::SECTIONS,
                $this->startRange->copy(),
                $this->endRange->copy()
            ))
                ->data(true);

            $providerData['name']    = $provider->display_name;
            $providerData['start']   = $this->startRange;
            $providerData['end']     = $this->endRange;
            $providerData['isEmail'] = true;

            if ($this->tester) {
                $this->tester->notify(new WeeklyProviderReport($providerData));
            } else {
                $provider->notify(new WeeklyProviderReport($providerData));
            }
        }
    }
}
