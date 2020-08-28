<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;

abstract class AbstractProcessor implements PatientChargeableServiceProcessor
{
    private PatientProcessorEloquentRepository $repo;

    public function attach(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary
    {
        return $this->repo()->store($patientId, $this->code(), $chargeableMonth);
    }

    public function fulfill(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo()->fulfill($patientId, $this->code(), $chargeableMonth);

        if (method_exists($this, 'attachNext')) {
            $this->attachNext($patientId, $chargeableMonth);
        }

        return $summary;
    }

    public function isAttached(int $patientId, Carbon $chargeableMonth): bool
    {
        return $this->repo()->isAttached($patientId, $this->code(), $chargeableMonth);
    }

    public function isFulfilled(int $patientId, Carbon $chargeableMonth): bool
    {
        return $this->repo()->isFulfilled($patientId, $this->code(), $chargeableMonth);
    }

    public function processBilling(int $patientId, Carbon $chargeableMonth)
    {
        if ( ! $this->isAttached($patientId, $chargeableMonth)) {
            return;
        }

        if ($this->isFulfilled($patientId, $chargeableMonth)) {
            return;
        }

        if ($this->shouldFulfill($patientId, $chargeableMonth)) {
            $this->fulfill($patientId, $chargeableMonth);
        }
    }

    public function repo(): PatientProcessorEloquentRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientProcessorEloquentRepository::class);
        }

        return $this->repo;
    }

    public function shouldAttach(Carbon $chargeableMonth, PatientProblemForProcessing ...$patientProblems): bool
    {
        return collect($patientProblems)->filter(
            fn (PatientProblemForProcessing $problem) => collect($problem->getServiceCodes())->contains($this->code())
        )->filter()->count() >= $this->minimumNumberOfProblems();
    }

    public function shouldFulfill(int $patientId, Carbon $chargeableMonth): bool
    {
        // TODO: Implement shouldFulfill() method.
    }
}
