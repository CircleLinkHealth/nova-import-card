<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Contracts\ChunksEloquentBuilder;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLocationPatientsChunk implements ChunksEloquentBuilder, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected AvailableServiceProcessors $availableServiceProcessors;

    protected Builder $builder;

    protected Carbon $chargeableMonth;

    protected int $limit;

    protected int $offset;

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

    public function getLimit()
    {
        return $this->limit;
    }

    public function getOffset()
    {
        return $this->offset;
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

    public function setBuilder(int $offset, int $limit, Builder $builder): self
    {
        $this->builder = $builder
            ->offset($this->offset = $offset)
            ->limit($this->limit = $limit);

        return $this;
    }
}
