<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Traits\PropagatesSequence;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class CCM implements PatientChargeableServiceProcessor
{
    use PropagatesSequence;

    private PatientProcessorEloquentRepository $repo;

    public function attach(int $patientId, Carbon $monthYear): ChargeablePatientMonthlySummary
    {
        return $this->repo()->store($patientId, $this->code(), $monthYear);
    }

    public function code(): string
    {
        return ChargeableService::CCM;
    }

    public function fulfill(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement fulfill() method.

        $this->attachNext($patientId, $monthYear);
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

    public function minimumTimeInSeconds(): int
    {
        // TODO: Implement minimumTimeInSeconds() method.
    }

    public function next(): PatientChargeableServiceProcessor
    {
        return new CCM40();
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
        return true;
    }

    public function shouldFulfill(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement shouldFulfill() method.
    }
}
