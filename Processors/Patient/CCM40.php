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

class CCM40 implements PatientChargeableServiceProcessor
{
    use PropagatesSequence;

    private PatientProcessorEloquentRepository $repo;

    public function attach(int $patientId, Carbon $monthYear): ChargeablePatientMonthlySummary
    {
        // TODO: Implement attach() method.
    }

    public function code(): string
    {
        return ChargeableService::CCM_PLUS_40;
    }

    public function fulfill(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo()->fulfill($patientId, $this->code(), $chargeableMonth);

        $this->attachNext($patientId, $chargeableMonth);

        return $summary;
    }

    public function isAttached(int $patientId, Carbon $monthYear): bool
    {
        // TODO: Implement isAttached() method.
    }

    public function isFulfilled(int $patientId, Carbon $monthYear): bool
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

    public function next(): PatientChargeableServiceProcessor
    {
        return new CCM60();
    }

    public function processBilling(int $patientId, Carbon $monthYear): bool
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

    public function shouldFulfill(int $patientId, Carbon $monthYear): bool
    {
        // TODO: Implement shouldFulfill() method.
    }
}
