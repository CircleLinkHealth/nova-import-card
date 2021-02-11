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

    protected array $forcedPatientServices;

    protected array $patientServices;

    /**
     * @return array
     */
    public function getPatientServices(): array
    {
        return $this->patientServices;
    }

    /**
     * @param array $patientServices
     */
    public function setPatientServices(array $patientServices): self
    {
        $this->patientServices = $patientServices;
        return $this;
    }

    protected int $locationId;

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

    public function getForcedPatientServices(): array
    {
        return $this->forcedPatientServices;
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getPatientProblems(): array
    {
        return $this->patientProblems;
    }

    public function ofLocation(int $locationId): self
    {
        $this->locationId = $locationId;

        return $this;
    }

    public function subscribe(AvailableServiceProcessors $availableServiceProcessors): self
    {
        $this->availableServiceProcessors = $availableServiceProcessors;

        return $this;
    }

    /**
     * @param array $forcedPatientServices
     *
     * @return PatientMonthlyBillingDTO
     */
    public function withForcedPatientServices(ForcedPatientChargeableServicesForProcessing ...$forcedPatientServices): self
    {
        $this->forcedPatientServices = $forcedPatientServices;

        return $this;
    }

    public function withProblems(PatientProblemForProcessing ...$patientProblems): self
    {
        $this->patientProblems = $patientProblems;

        return $this;
    }
}
