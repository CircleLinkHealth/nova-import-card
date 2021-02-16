<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientProblemsForBillingProcessing;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\Customer\Entities\User;

class PatientMonthlyBillingDTO
{
    protected AvailableServiceProcessors $availableServiceProcessors;

    protected bool $billingStatusIsTouched = false;

    protected Carbon $chargeableMonth;

    protected array $forcedPatientServices = [];

    protected int $locationId;

    protected array $locationServices = [];

    protected int $patientId;

    protected array $patientProblems;

    protected array $patientServices = [];

    protected array $practiceServiceCodes = [];

    public function billingStatusIsTouched(): bool
    {
        return $this->billingStatusIsTouched;
    }

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

    public static function generateFromUser(User $patient, Carbon $month): self
    {
        return (new self())
            ->subscribe($patient->patientInfo->location->availableServiceProcessors($month))
            ->forPatient($patient->id)
            ->ofLocation($patient->patientInfo->location->id)
            ->forMonth($month)
            ->setBillingStatusIsTouched(
                ! is_null(optional($patient->monthlyBillingStatus
                    ->filter(fn (PatientMonthlyBillingStatus $mbs) => $mbs->chargeable_month->equalTo($month))
                    ->first())->actor_id)
            )
            ->withLocationServices(
                ...LocationChargeableServicesForProcessing::fromCollection($patient->patientInfo->location->chargeableServiceSummaries)
            )
            ->withPracticeServiceCodes($patient->primaryPractice->chargeableServices->pluck('code')->toArray())
            ->withPatientServices(
                ...PatientChargeableServicesForProcessing::fromCollection($patient)
            )
            ->withForcedPatientServices(
                ...ForcedPatientChargeableServicesForProcessing::fromCollection($patient->forcedChargeableServices)
            )
            ->withProblems(...PatientProblemsForBillingProcessing::getArrayFromPatient($patient));
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

    public function getLocationServices(): array
    {
        return $this->locationServices;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getPatientProblems(): array
    {
        return $this->patientProblems;
    }

    public function getPatientServices(): array
    {
        return $this->patientServices;
    }

    public function getPracticeCodes(): array
    {
        return $this->practiceServiceCodes;
    }

    public function ofLocation(int $locationId): self
    {
        $this->locationId = $locationId;

        return $this;
    }

    public function pushServiceFromOutputIfYouShould(PatientServiceProcessorOutputDTO $output): void
    {
        $exists = collect($this->getPatientServices())->filter(function (PatientChargeableServicesForProcessing $s) use ($output) {
            return $s->getCode() === $output->getCode();
        })->isNotEmpty();

        if ( ! $exists) {
            $this->patientServices[] = $output->toPatientChargeableServiceForProcessingDTO();
        }
    }

    public function setBillingStatusIsTouched(bool $isTouched): self
    {
        $this->billingStatusIsTouched = $isTouched;

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

    /**
     * @param array $locationServices
     */
    public function withLocationServices(LocationChargeableServicesForProcessing ...$locationServices): self
    {
        $this->locationServices = $locationServices;

        return $this;
    }

    /**
     * @param array $patientServices
     */
    public function withPatientServices(PatientChargeableServicesForProcessing ...$patientServices): self
    {
        $this->patientServices = $patientServices;

        return $this;
    }

    public function withPracticeServiceCodes(array $serviceCodes): self
    {
        $this->practiceServiceCodes = $serviceCodes;

        return $this;
    }

    public function withProblems(PatientProblemForProcessing ...$patientProblems): self
    {
        $this->patientProblems = $patientProblems;

        return $this;
    }
}
