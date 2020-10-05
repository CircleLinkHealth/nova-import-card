<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;

abstract class AbstractProcessor implements PatientServiceProcessor
{
    private PatientServiceProcessorRepository $repo;

    public function attach(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary
    {
        return $this->repo()->store($patientId, $this->code(), $chargeableMonth, $this->requiresPatientConsent($patientId));
    }

    public function clashesWith(): array
    {
        return [
        ];
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

    public function processBilling(PatientMonthlyBillingDTO $patientStub): void
    {
        if ( ! $this->isAttached($patientStub->getPatientId(), $patientStub->getChargeableMonth())) {
            if ($this->shouldAttach(
                $patientStub->getPatientId(),
                $patientStub->getChargeableMonth(),
                ...$patientStub->getPatientProblems()
            )) {
                $this->attach($patientStub->getPatientId(), $patientStub->getChargeableMonth());
            }
        }

        if ( ! $this->isFulfilled($patientStub->getPatientId(), $patientStub->getChargeableMonth())) {
            if ($this->shouldFulfill(
                $patientStub->getPatientId(),
                $patientStub->getChargeableMonth(),
                ...$patientStub->getPatientProblems()
            )) {
                $this->fulfill($patientStub->getPatientId(), $patientStub->getChargeableMonth());
            }
        }
    }

    public function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }

    abstract public function requiresPatientConsent(int $patientId): bool;

    public function shouldAttach(int $patientId, Carbon $chargeableMonth, PatientProblemForProcessing ...$patientProblems): bool
    {
        if ($this->clashesWithHigherOrderServices($patientId, $chargeableMonth)) {
            return false;
        }

        if ($this->hasUnfulfilledPreviousService($patientId, $chargeableMonth)) {
            return false;
        }

        return collect($patientProblems)
            ->filter(
                function (PatientProblemForProcessing $problem) use ($patientId, $chargeableMonth) {
                    return collect($problem->getServiceCodes())->contains($this->code());
                }
            )->count() >= $this->minimumNumberOfProblems();
    }

    public function shouldFulfill(int $patientId, Carbon $chargeableMonth, PatientProblemForProcessing ...$patientProblems): bool
    {
        if ( ! $this->shouldAttach($patientId, $chargeableMonth, ...$patientProblems)) {
            return false;
        }

        $summary = $this->repo()
            ->getChargeablePatientSummary($patientId, $this->code(), $chargeableMonth);

        if ( ! $summary) {
            return false;
        }

        if ($summary->requires_patient_consent) {
            return false;
        }

        if ($summary->total_time < $this->minimumTimeInSeconds()) {
            return false;
        }

        if ($summary->no_of_successful_calls < $this->minimumNumberOfCalls()) {
            return false;
        }

        return true;
    }

    private function clashesWithHigherOrderServices(int $patientId, Carbon $chargeableMonth): bool
    {
        foreach ($this->clashesWith() as $clash) {
            if ($this->repo()->isAttached($patientId, $clash->code(), $chargeableMonth)) {
                return true;
            }
        }

        return false;
    }

    private function hasUnfulfilledPreviousService(int $patientId, Carbon $chargeableMonth): bool
    {
        if ( ! method_exists($this, 'previous')) {
            return false;
        }

        if ( ! $this->previous() instanceof PatientServiceProcessor) {
            return false;
        }

        if ( ! $this->repo()->isFulfilled($patientId, $this->previous()->code(), $chargeableMonth)) {
            return true;
        }

        return false;
    }
}
