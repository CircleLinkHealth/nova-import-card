<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingStub;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;

abstract class AbstractProcessor implements PatientServiceProcessor
{
    private PatientServiceProcessorRepository $repo;

    public function attach(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary
    {
        return $this->repo()->store($patientId, $this->code(), $chargeableMonth);
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

    public function processBilling(PatientMonthlyBillingStub $patientStub): void
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

    public function shouldAttach(int $patientId, Carbon $chargeableMonth, PatientProblemForProcessing ...$patientProblems): bool
    {
        return collect($patientProblems)
            ->filter(
                function (PatientProblemForProcessing $problem) use ($patientId, $chargeableMonth) {
                    foreach ($this->clashesWith() as $clash) {
                        if ($this->repo()->isAttached($patientId, $clash->code(), $chargeableMonth)) {
                            return false;
                        }
                    }

                    if (method_exists($this, 'previous')) {
                        if ($this->previous() instanceof PatientServiceProcessor) {
                            if ( ! $this->repo()->isFulfilled($patientId, $this->previous()->code(), $chargeableMonth)) {
                                return false;
                            }
                        }
                    }

                    return collect($problem->getServiceCodes())->contains($this->code());
                }
            )->filter()->count() >= $this->minimumNumberOfProblems();
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
        
        if ($summary->time_for_month_to_be_implemented < $this->minimumTimeInSeconds()) {
            return false;
        }

        if ($summary->calls_for_month_to_be_implemented < $this->minimumNumberOfCalls()) {
            return false;
        }

        return true;
    }
}
