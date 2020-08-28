<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateLocationSummaries implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $locationId;

    protected Carbon $monthYear;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $locationId, Carbon $monthYear)
    {
        $this->locationId = $locationId;
        $this->monthYear  = $monthYear;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ChargeableLocationMonthlySummary::where('location_id', $this->locationId)
            ->where('chargeable_month', $this->monthYear->copy()->subMonth(1))
            ->get()
            ->each(function (ChargeableLocationMonthlySummary $clms) {
                ChargeableLocationMonthlySummary::updateOrCreate(
                    [
                        'location_id'           => $this->locationId,
                        'chargeable_service_id' => $clms->chargeable_service_id,
                        'chargeable_month'      => $this->monthYear,
                    ],
                    [
                        'amount' => $clms->amount,
                    ]
                );
            });
    }
}
