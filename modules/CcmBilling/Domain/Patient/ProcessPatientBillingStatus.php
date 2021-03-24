<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientSummaryForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Support\Facades\Log;

class ProcessPatientBillingStatus
{
    private PatientMonthlyBillingDTO $dto;
    private ?Carbon $month  = null;
    private ?string $status = null;

    public static function fromDTO(PatientMonthlyBillingDTO $dto)
    {
        if ($dto->billingStatusIsTouched()) {
            return;
        }
        (new static())
            ->setDto($dto)
            ->autoAttestIfYouShould()
            ->determineBillingStatus()
            ->updateOrCreateModel();
    }

    public function setMonth(Carbon $month): ProcessPatientBillingStatus
    {
        $this->month = $month;

        return $this;
    }

    public function setPatientId(int $patientId): ProcessPatientBillingStatus
    {
        $this->patientId = $patientId;

        return $this;
    }

    private function attestedCountForService(string $service): int
    {
        if (ChargeableService::CCM === $service && ! $this->fulfilledSummaryForService(ChargeableService::BHI)) {
            $service = null;
        }

        return collect($this->dto->getPatientProblems())
            ->filter(function (PatientProblemForProcessing $p) use ($service) {
                if (is_null($service)) {
                    return $p->isAttestedForMonth();
                }

                return in_array($service, $p->getServiceCodes()) && $p->isAttestedForMonth();
            })
            ->count();
    }

    private function autoAttestIfYouShould(): self
    {
        //todo: optimise or remove
        AutoPatientAttestation::fromId($this->dto->getPatientId())
            ->setMonth($this->dto->getChargeableMonth())
            ->executeIfYouShould();

        return $this;
    }

    private function determineBillingStatus(): self
    {
        $needsQA = [];

        if (! $this->hasAnyFulfilledServices()){
            $needsQA[] = 'Patient does not have any fulfilled Chargeable Service Summaries';
        }

        if ($this->unAttestedProblems()) {
            $needsQA[] = 'Patient does not have enough attested conditions.';
        }

        if (0 === $this->dto->getSuccessfulCallsCount()) {
            $needsQA[] = '0 successful calls';
        }

        if ( ! $this->dto->billingProviderExists()) {
            $needsQA[] = 'No billing provider';
        }

        if (in_array($this->dto->getCcmStatusForMonth(), [Patient::WITHDRAWN, Patient::PAUSED, Patient::WITHDRAWN_1ST_CALL])) {
            $needsQA[] = 'Patient not enrolled.';
        }

        if (! empty($needsQA)){
            $reasonsString = implode(',', $needsQA);
            Log::info("Billing: Patient {$this->dto->getPatientId()} needs QA for month {$this->dto->getChargeableMonth()->toDateString()} for the following reasons: $reasonsString");
            $this->status = PatientMonthlyBillingStatus::NEEDS_QA;
        }else {
            $this->status = PatientMonthlyBillingStatus::APPROVED;
        }

        return $this;
    }

    private function fulfilledSummaryForService(string $service): bool
    {
        return collect($this->dto->getPatientServices())
            ->filter(fn (PatientSummaryForProcessing $s) => $s->getCode() === $service && $s->isFulfilled())
            ->isNotEmpty();
    }

    private function setDto(?PatientMonthlyBillingDTO $dto): self
    {
        $this->dto = $dto;

        return $this;
    }

    private function unAttestedProblems(): bool
    {
        foreach ([
            ChargeableService::BHI,
            ChargeableService::CCM,
            ChargeableService::PCM,
            ChargeableService::GENERAL_CARE_MANAGEMENT,
        ] as $service) {
            if ($this->unAttestedService($service)) {
                return true;
            }
        }

        return false;
    }

    private function unAttestedService(string $service): bool
    {
        return $this->fulfilledSummaryForService($service)
               && $this->attestedCountForService($service) < (optional(ChargeableService::getProcessorForCode($service))->minimumNumberOfProblems() ?? 0);
    }

    private function updateOrCreateModel()
    {
        PatientMonthlyBillingStatus::updateOrCreate([
            'patient_user_id'  => $this->dto->getPatientId(),
            'chargeable_month' => $this->dto->getChargeableMonth(),
        ], [
            'status' => $this->status,
        ]);
    }

    private function hasAnyFulfilledServices() : bool
    {
        return collect($this->dto->getPatientServices())
            ->filter(fn (PatientSummaryForProcessing $s) => $s->isFulfilled())
            ->isNotEmpty();
    }
}
