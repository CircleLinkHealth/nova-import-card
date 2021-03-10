<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Domain\Patient\ClashingChargeableServices;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use CircleLinkHealth\CcmBilling\ValueObjects\ForcedPatientChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\LocationChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientSummaryForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientServiceProcessorOutputDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientTimeForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;

abstract class AbstractProcessor implements PatientServiceProcessor
{
    private PatientMonthlyBillingDTO $input;
    private PatientServiceProcessorOutputDTO $output;

    public function __construct()
    {
        $this->output = new PatientServiceProcessorOutputDTO();
    }

    public function attach(): void
    {
        $requiresConsent = $this->requiresPatientConsent($this->input->getPatientId());

        if ($existingSummary = $this->getExistingSummary($this->code())) {
            $existingSummary->setRequiresConsent($requiresConsent);
        }

        $this->output->setRequiresConsent($requiresConsent)
            ->setSendToDatabase(true);
    }

    public function baseCode(): string
    {
        return $this->code();
    }

    public function clashesWith(): array
    {
        return ClashingChargeableServices::getProcessorsForClashesOfService($this->code());
    }

    public function fulfill(): void
    {
        $this->output->setSendToDatabase(true)
            ->setIsFulfilling(true);
    }

    public function hasEnoughProblems(): bool
    {
        return collect($this->input->getPatientProblems())
            ->filter(
                function (PatientProblemForProcessing $problem) {
                    return collect($problem->getServiceCodes())->contains($this->baseCode());
                }
            )->count() >= $this->minimumNumberOfProblems();
    }

    public function isAttached(): bool
    {
        return ! is_null($this->getExistingSummary($this->code()));
    }

    public function isBlocked(): bool
    {
        return collect($this->input->getForcedPatientServices())->filter(
            fn (ForcedPatientChargeableServicesForProcessing $s) => in_array($s->getChargeableServiceCode(), [$this->code(), $this->baseCode()]) && $s->isBlocked()
        )
            ->isNotEmpty();
    }

    public function isEligibleForPatient(PatientMonthlyBillingDTO $patient): bool
    {
        return $this->shouldForceAttach() || $this->shouldAttach();
    }

    public function isFulfilled(): bool
    {
        return collect($this->input->getPatientServices())
            ->filter(fn (PatientSummaryForProcessing $s) => $s->getCode() === $this->code() && $s->isFulfilled())
            ->isNotEmpty();
    }

    public function processBilling(PatientMonthlyBillingDTO $patientStub): PatientServiceProcessorOutputDTO
    {
        return $this->setInput($patientStub)
            ->initialiseOutput()
            ->getOutput();
    }

    abstract public function requiresPatientConsent(int $patientId): bool;

    public function shouldAttach(): bool
    {
        if ( ! $this->featureIsEnabled()) {
            return false;
        }

        if ( ! $this->isEnabledForLocation()) {
            return false;
        }

        if ($this->shouldForceAttach()) {
            return true;
        }

        if ($this->isBlocked()) {
            return false;
        }

        if ( ! $this->hasEnoughProblems()) {
            return false;
        }

        if ($this->isClashForForcedService()) {
            return false;
        }

        if ($this->clashesWithHigherOrderServices()) {
            return false;
        }

        return true;
    }

    public function shouldForceAttach(): bool
    {
        return collect($this->input->getForcedPatientServices())->filter(
            fn (ForcedPatientChargeableServicesForProcessing $s) => $s->getChargeableServiceCode() == $this->baseCode() && $s->isForced()
        )
            ->isNotEmpty();
    }

    public function shouldFulfill(): bool
    {
        if ( ! $this->shouldAttach()) {
            return false;
        }

        if ($this->requiresPatientConsent($this->input->getPatientId())) {
            return false;
        }

        if ($this->timeForService() < $this->minimumTimeInSeconds()) {
            return false;
        }

        return true;
    }

    public function shouldUnfulfill(): bool
    {
        return ! $this->shouldFulfill();
    }

    public function unfulfill()
    {
        $this->output->setSendToDatabase(true)
            ->setIsFulfilling(false);
    }

    private function clashesWithHigherOrderServices(): bool
    {
        foreach ($this->clashesWith() as $clash) {
            $clash->setInput($this->input);

            if ($clash->isEligibleForPatient($this->input)) {
                return true;
            }
        }

        return false;
    }

    private function getExistingSummary(string $code): ?PatientSummaryForProcessing
    {
        return collect($this->input->getPatientServices())
            ->filter(fn (PatientSummaryForProcessing $s) => $s->getCode() === $code)
            ->first();
    }

    private function getOutput(): PatientServiceProcessorOutputDTO
    {
        if ( ! $this->isAttached()) {
            if ($this->shouldAttach()) {
                $this->attach();
            }
        }

        if ( ! $this->isFulfilled()) {
            if ($this->shouldFulfill()) {
                $this->fulfill();
            }
        } else {
            if ($this->shouldUnfulfill()) {
                $this->unfulfill();
            }
        }

        return $this->output;
    }

    private function initialiseOutput(): self
    {
        $this->output->setPatientUserId($this->input->getPatientId())
            ->setChargeableMonth($this->input->getChargeableMonth())
            ->setCode($this->code());

        if ($existingSummary = $this->getExistingSummary($this->code())) {
            $this->output->setChargeableServiceId($existingSummary->getChargeableServiceId())
                ->setRequiresConsent($existingSummary->requiresConsent());
        } else {
            $this->output->setChargeableServiceId(ChargeableService::getChargeableServiceIdUsingCode($this->code()));
        }

        return $this;
    }

    private function isClashFor(): array
    {
        return ClashingChargeableServices::getProcessorsServiceIsClashFor($this->code());
    }

    private function isClashForForcedService(): bool
    {
        foreach ($this->isClashFor() as $processor) {
            if ($processor->setInput($this->input)->shouldForceAttach()) {
                return true;
            }
        }

        return false;
    }

    private function isEnabledForLocation(): bool
    {
        return collect($this->input->getLocationServices())
            ->filter(fn (LocationChargeableServicesForProcessing $s) => $s->getCode() === $this->code())
            ->isNotEmpty();
    }

    private function setInput(PatientMonthlyBillingDTO $input): self
    {
        $this->input = $input;

        return $this;
    }

    private function timeForService():int
    {
        return optional(collect($this->input->getPatientTimes())
            ->filter(fn(PatientTimeForProcessing $time) => $time->getCode() === $this->baseCode())
            ->first())->getTime() ?? 0;
    }
}
