<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class PatientMonthlyBillingStub
{
    protected AvailableServiceProcessors $availableServiceProcessors;

    protected Carbon $chargeableMonth;

    protected Collection $patientProblems;

    public function getAvailableServiceProcessors(): AvailableServiceProcessors
    {
        return $this->availableServiceProcessors;
    }

    public function getChargeableMonth(): Carbon
    {
        return $this->chargeableMonth;
    }

    public function getPatientProblems(): Collection
    {
        return $this->patientProblems;
    }

    public function setAvailableServiceProcessors(AvailableServiceProcessors $availableServiceProcessors): void
    {
        $this->availableServiceProcessors = $availableServiceProcessors;
    }

    public function setChargeableMonth(Carbon $chargeableMonth): void
    {
        $this->chargeableMonth = $chargeableMonth;
    }

    public function setPatientProblems(Collection $patientProblems): void
    {
        $this->patientProblems = $patientProblems;
    }
}
