<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Processors\Customer\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLocationPatientMonthlyServices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Carbon $chargeableMonth;

    protected int $locationId;

    protected Location $processor;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $locationId, Carbon $chargeableMonth)
    {
        $this->locationId      = $locationId;
        $this->chargeableMonth = $chargeableMonth;
        $this->processor       = app(Location::class);
    }

    public function getChargeableMonth(): Carbon
    {
        return $this->chargeableMonth;
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }

    public function getProcessor(): Location
    {
        return $this->processor;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getProcessor()->processServicesForAllPatients($this->getLocationId(), $this->getChargeableMonth());
    }
}
