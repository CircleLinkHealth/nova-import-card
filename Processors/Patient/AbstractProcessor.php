<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use CircleLinkHealth\CcmBilling\ValueObjects\ForcedPatientChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientServiceProcessorOutputDTO;
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
        $this->output->setSendToDatabase(true);
    }

    public function baseCode(): string
    {
        return $this->code();
    }

    public function clashesWith(): array
    {
        return [
        ];
    }

    public function fulfill(): void
    {
        $this->output->setSendToDatabase(true);
        $this->output->setIsFulfilling(true);
    }

    public function isAttached(): bool
    {
        return collect($this->input->getPatientServices())
            ->filter(fn(PatientChargeableServicesForProcessing $s) => $s->getCode() === $this->code())
            ->isNotEmpty();
    }

    public function isFulfilled(): bool
    {
        return collect($this->input->getPatientServices())
            ->filter(fn(PatientChargeableServicesForProcessing $s) => $s->getCode() === $this->code() && $s->isFulfilled())
            ->isNotEmpty();
    }

    public function processBilling(PatientMonthlyBillingDTO $patientStub): PatientServiceProcessorOutputDTO
    {
        $this->input = $patientStub;
        return $this->getOutput();
    }

    private function getOutput(): PatientServiceProcessorOutputDTO
    {
        $this->output->setPatientUserId($this->input->getPatientId());
        $this->output->setChargeableServiceId(ChargeableService::cached()->where('code', $this->code())->first()->id);

        if ( ! $this->isAttached()) {
            if ($this->shouldForceAttach() || $this->shouldAttach()) {
                $this->attach();
            }
        }

        if ( ! $this->isFulfilled()) {
            if ($this->shouldFulfill()) {
                $this->fulfill();
            }
        }else{
            if ($this->shouldUnfulfill()){
                $this->unfulfill();
            }
        }
    }

    private function shouldUnfulfill() : bool
    {
        //todo
        //has clash/force, block
        //no longer has enough problems
    }

    private function unfulfill()
    {
        $this->output->setSendToDatabase(true);
        $this->output->setIsFulfilling(false);
    }

    abstract public function requiresPatientConsent(int $patientId): bool;

    public function shouldAttach(): bool
    {
        if ( ! $this->featureIsEnabled()) {
            return false;
        }

        if ($this->clashesWithHigherOrderServices()) {
            return false;
        }

        return collect($this->input->getPatientProblems())
            ->filter(
                function (PatientProblemForProcessing $problem) {
                    return collect($problem->getServiceCodes())->contains($this->baseCode());
                }
            )->count() >= $this->minimumNumberOfProblems();
    }

    public function shouldForceAttach()
    {
        return collect($this->input->getForcedPatientServices())->filter(
            fn (ForcedPatientChargeableServicesForProcessing $s) => $s->getChargeableServiceCode() == $this->code() && ! $s->isForced()
        )
            ->isNotEmpty();
    }

    public function shouldFulfill(): bool
    {
        if (! $this->shouldAttach() ) {
            return false;
        }

        //todo: change codeForProblems name to 'base code' or something
        $summary = collect($this->input->getPatientServices())
            ->filter(fn(PatientChargeableServicesForProcessing $s) => $s->getCode() === $this->code())
            ->first();

        if ( ! $summary) {
            return false;
        }

        if ($summary->requiresConsent()) {
            return false;
        }

        //make sure to add monthly time to DTO like below:
//        /** @var ChargeablePatientMonthlyTime $monthlyTime */
//        $monthlyTime = $patient
//            ->chargeableMonthlyTime
//            ->where('chargeableService.code', $this->baseCode())
//            ->where('chargeable_month', $chargeableMonth)
//            ->first();
//
//        if ( ! $monthlyTime) {
//            return false;
//        }
        if ($summary->getMonthlyTime() < $this->minimumTimeInSeconds()) {
            return false;
        }

        return true;
    }

    private function clashesWithHigherOrderServices(): bool
    {
        //todo: revisit clashes to accomodate forced cs - if forced === is attached?
        foreach ($this->clashesWith() as $clash) {
            $clashIsAttached = collect($this->input->getPatientServices())->filter(fn(PatientChargeableServicesForProcessing $s) => $s->getCode() === $clash)->isNotEmpty();

            $hasEnoughProblemsForClash = collect($this->input->getPatientProblems())
                ->filter(fn (PatientProblemForProcessing $problem) => in_array($clash->code(), $problem->getServiceCodes()))
                ->count() >= $clash->minimumNumberOfProblems();

            if ($clashIsAttached && $hasEnoughProblemsForClash) {
                return true;
            }
        }

        return false;
    }
}
