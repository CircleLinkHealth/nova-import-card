<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Entities\User;

class ProcessPatientSummaries
{
    protected Carbon $month;

    protected PatientMonthlyBillingDTO $patientDTO;

    protected int $patientId;

    protected User $patientUser;
    protected PatientMonthlyBillingProcessor $processor;

    protected PatientProcessorEloquentRepository $repo;

    public function __construct(PatientMonthlyBillingProcessor $processor, PatientProcessorEloquentRepository $repo)
    {
        $this->processor = $processor;
        $this->repo      = $repo;
    }
    
    public function execute(int $patientId, Carbon $month): void
    {
        $this->setPatientId($patientId)
            ->setMonth($month)
            ->setPatientUser()
            ->setPatientDto()
            ->process();
    }

    //maybe it can hold the value of summary and problem existence to assist with helper checks?
    public function fromDTO(PatientMonthlyBillingDTO $dto): void
    {
        $this->setPatientDto($dto)
            ->process();
    }

    private function process()
    {
        $this->processor->process($this->patientDTO);
    }

    private function setMonth(Carbon $month): self
    {
        $this->month = $month;

        return $this;
    }

    private function setPatientDto(PatientMonthlyBillingDTO $dto = null): self
    {
        if ( ! is_null($dto)) {
            $this->patientDTO = $dto;

            return $this;
        }

        if (is_null($this->patientUser)) {
            sendSlackMessage('#billing_alerts', "Patient ({$this->patientId}) does not have location attached. Cannot Process Billing, please investigate");

            return $this;
        }

        if (is_null($this->patientUser->patientInfo->location)) {
            sendSlackMessage('#billing_alerts', "Patient ({$this->patientUser->id}) does not have location attached. Cannot Process Billing, please investigate");

            return $this;
        }

        $this->patientDTO = (new PatientMonthlyBillingDTO())
            ->subscribe($this->patientUser->patientInfo->location->availableServiceProcessors($this->month))
            ->forPatient($this->patientUser->id)
            ->forMonth($this->month)
            ->withProblems(...$this->patientUser->patientProblemsForBillingProcessing()->toArray());

        return $this;
    }

    private function setPatientId(int $patientId): self
    {
        $this->patientId = $patientId;

        return $this;
    }

    private function setPatientUser(): self
    {
        $this->patientUser = $this->repo
            ->patientWithBillingDataForMonth($this->patientId, $this->month)
            ->first();

        return $this;
    }
}
