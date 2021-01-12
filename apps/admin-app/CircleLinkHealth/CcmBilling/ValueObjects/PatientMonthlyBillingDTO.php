<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use Carbon\Carbon;

class PatientMonthlyBillingDTO
{
    protected AvailableServiceProcessors $availableServiceProcessors;

    protected Carbon $chargeableMonth;

    protected int $patientId;

    protected array $patientProblems;

    public function forMonth(Carbon $chargeableMonth): self
    {
        $this->chargeableMonth = $chargeableMonth;

        return $this;
    }

    public function forPatient(int $patientId): self
    {
        $this->patientId = $patientId;

        return $this;
    }

    public function getAvailableServiceProcessors(): AvailableServiceProcessors
    {
        return $this->availableServiceProcessors;
    }

    public function getChargeableMonth(): Carbon
    {
        return $this->chargeableMonth;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getPatientProblems(): array
    {
        return $this->patientProblems;
    }

    public function subscribe(AvailableServiceProcessors $availableServiceProcessors): self
    {
        $this->availableServiceProcessors = $availableServiceProcessors;

        return $this;
    }

    public function withProblems(PatientProblemForProcessing ...$patientProblems): self
    {
        $this->patientProblems = $patientProblems;

        return $this;
    }
}
