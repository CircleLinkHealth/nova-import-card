<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLocationPatientsChunk extends ChunksEloquentBuilderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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

    public function getAvailableServiceProcessors()
    {
        return $this->availableServiceProcessors;
    }

    public function getChargeableMonth()
    {
        return $this->chargeableMonth;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->builder->get()->each(function (User $patient) {
            ProcessPatientMonthlyServices::dispatch(
                (new PatientMonthlyBillingDTO())
                    ->subscribe($this->getAvailableServiceProcessors())
                    ->forPatient($patient->id)
                    ->forMonth($this->getChargeableMonth())
                    ->withProblems($patient->patientProblemsForBillingProcessing()->toArray())
            );
        });
    }
}
