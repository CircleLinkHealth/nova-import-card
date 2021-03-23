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

    protected bool $billingProviderExists = false;

    protected PatientMonthlyBillingStatusDTO $billingStatus;

    protected ?string $ccmStatusForMonth = null;

    protected Carbon $chargeableMonth;

    protected array $forcedPatientServices = [];

    protected int $locationId;

    protected array $locationServices = [];

    protected int $patientId;

    protected array $patientProblems;

    protected array $patientServices = [];

    protected array $patientTimes = [];

    protected array $practiceServiceCodes = [];

    protected int $successfulCalls = 0;

    public function billingProviderExists(): bool
    {
        return $this->billingProviderExists;
    }

    public function billingStatusIsTouched(): bool
    {
        return $this->billingStatus->isTouched();
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
            ->forMonth($month->copy()->startOfMonth())
            ->setBillingStatus(
                PatientMonthlyBillingStatusDTO::fromModel(
                    $patient->monthlyBillingStatus
                        ->filter(fn (PatientMonthlyBillingStatus $mbs) => $mbs->chargeable_month->equalTo($month))
                        ->first()
                )
            )
            ->setBillingProviderExists( ! is_null($patient->billingProviderUser()))
            ->setCcmStatusForMonth($patient->getCcmStatusForMonth($month->copy()))
            ->setSuccessfulCalls($patient->inboundSuccessfulCalls->count())
            ->withLocationServices(
                ...LocationChargeableServicesForProcessing::fromCollection($patient->patientInfo->location->chargeableServiceSummaries)
            )
            ->withPracticeServiceCodes($patient->primaryPractice->chargeableServices->pluck('code')->toArray())
            ->withPatientServices(
                ...PatientSummaryForProcessing::fromCollection($patient->chargeableMonthlySummaries)
            )
            ->withPatientMonthlyTimes(...PatientTimeForProcessing::fromCollection($patient->chargeableMonthlyTime))
            ->withForcedPatientServices(
                ...ForcedPatientChargeableServicesForProcessing::fromCollection($patient->forcedChargeableServices)
            )
            ->withProblems(...PatientProblemsForBillingProcessing::getArrayFromPatient($patient));
    }

    public function getAvailableServiceProcessors(): AvailableServiceProcessors
    {
        return $this->availableServiceProcessors;
    }

    public function getBillingStatus(): PatientMonthlyBillingStatusDTO
    {
        return $this->billingStatus;
    }

    public function getCcmStatusForMonth(): ?string
    {
        return $this->ccmStatusForMonth;
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

    public function getPatientTimes(): array
    {
        return $this->patientTimes;
    }

    public function getPracticeCodes(): array
    {
        return $this->practiceServiceCodes;
    }

    public function getSuccessfulCallsCount(): int
    {
        return $this->successfulCalls;
    }

    public function ofLocation(int $locationId): self
    {
        $this->locationId = $locationId;

        return $this;
    }

    public function setBillingProviderExists(bool $billingProviderExists): self
    {
        $this->billingProviderExists = $billingProviderExists;

        return $this;
    }

    public function setBillingStatus(PatientMonthlyBillingStatusDTO $billingStatus): self
    {
        $this->billingStatus = $billingStatus;

        return $this;
    }

    public function setCcmStatusForMonth(?string $ccmStatusForMonth): self
    {
        $this->ccmStatusForMonth = $ccmStatusForMonth;

        return $this;
    }

    public function setSuccessfulCalls(int $successfulCalls): self
    {
        $this->successfulCalls = $successfulCalls;

        return $this;
    }

    public function subscribe(AvailableServiceProcessors $availableServiceProcessors): self
    {
        $this->availableServiceProcessors = $availableServiceProcessors;

        return $this;
    }

    public function updateOrPushServiceFromOutput(PatientServiceProcessorOutputDTO $output): void
    {
        $this->patientServices = collect($this->getPatientServices())->filter(function (PatientSummaryForProcessing $s) use ($output) {
            return $s->getCode() != $output->getCode();
        })->push($output->toPatientChargeableServiceForProcessingDTO())->toArray();
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

    public function withPatientMonthlyTimes(PatientTimeForProcessing ...$times): self
    {
        $this->patientTimes = $times;

        return $this;
    }

    /**
     * @param array $patientServices
     */
    public function withPatientServices(PatientSummaryForProcessing ...$patientServices): self
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
