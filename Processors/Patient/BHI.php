<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class BHI implements PatientChargeableServiceProcessor
{
    private PatientProcessorEloquentRepository $repo;

    public function attach(int $patientId, Carbon $monthYear): ChargeablePatientMonthlySummary
    {
        return $this->repo()->store($patientId, $this->code(), $monthYear);
    }

    public function code()
    {
        return ChargeableService::BHI;
    }

    public function fulfill(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement fulfill() method.
    }

    public function isAttached(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement isAttached() method.
    }

    public function isFulfilled(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement isFulfilled() method.
    }

    public function minimumNumberOfCalls(): int
    {
        // TODO: Implement minimumNumberOfCalls() method.
    }

    public function minimumNumberOfProblems()
    {
        return 1;
    }

    public function minimumTimeInSeconds(): int
    {
        // TODO: Implement minimumTimeInSeconds() method.
    }

    public function processBilling(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement processBilling() method.
    }

    public function repo(): PatientProcessorEloquentRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientProcessorEloquentRepository::class);
        }

        return $this->repo;
    }

    public function shouldAttach($patientProblems, Carbon $monthYear)
    {
        return $patientProblems->where('code', $this->code())->count() >= $this->minimumNumberOfProblems();
    }

    public function shouldFulfill(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement shouldFulfill() method.
    }
}
