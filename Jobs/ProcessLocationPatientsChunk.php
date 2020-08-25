<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Contracts\ChunksEloquentBuilder;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\Customer\Entities\User;

class ProcessLocationPatientsChunk extends ChunksEloquentBuilder
{
    protected AvailableServiceProcessors $availableServiceProcessors;

    protected Carbon $chargeableMonth;

    /**
     * Create a new job instance.
     */
    public function __construct(AvailableServiceProcessors $availableServiceProcessors, Carbon $chargeableMonth)
    {
        $this->availableServiceProcessors = $availableServiceProcessors;
        $this->chargeableMonth            = $chargeableMonth;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->builder->get()->each(function (User $patient) {
            ProcessPatientMonthlyServices::dispatch($patient, $this->availableServiceProcessors);
        });
    }
}
