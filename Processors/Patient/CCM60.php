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

class CCM60 implements PatientChargeableServiceProcessor
{
    private PatientProcessorEloquentRepository $repo;

    public function attach(int $patientId, Carbon $monthYear): ChargeablePatientMonthlySummary
    {
        return $this->repo()->store($patientId, $this->code(), $monthYear);
    }

    public function code(): string
    {
        return ChargeableService::CCM_PLUS_60;
    }

    public function fulfill(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary
    {
        // TODO: Implement fulfill() method.
    }

    public function isAttached(int $patientId, Carbon $chargeableMonth): bool
    {
        // TODO: Implement isAttached() method.
    }

    public function isFulfilled(int $patientId, Carbon $chargeableMonth): bool
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

    public function name(): string
    {
        // TODO: Implement name() method.
    }

    public function processBilling(int $patientId, Carbon $chargeableMonth)
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

    public function shouldAttach($patientProblems, Carbon $monthYear): bool
    {
        // TODO: Implement shouldAttach() method.
    }

    public function shouldFulfill(int $patientId, Carbon $chargeableMonth): bool
    {
        // TODO: Implement shouldFulfill() method.
    }
}
