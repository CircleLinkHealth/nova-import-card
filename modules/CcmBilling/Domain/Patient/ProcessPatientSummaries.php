<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Entities\User;

class ProcessPatientSummaries
{
    protected Carbon $month;

    protected ?PatientMonthlyBillingDTO $patientDTO;

    protected int $patientId;

    protected ?User $patientUser;

    protected PatientMonthlyBillingProcessor $processor;

    protected PatientServiceProcessorRepository $repo;

    public function __construct(PatientMonthlyBillingProcessor $processor, PatientServiceProcessorRepository $repo)
    {
        $this->processor = $processor;
        $this->repo      = $repo;
    }

    public function execute(int $patientId, Carbon $month): void
    {
        measureTime("ProcessPatientSummaries::execute:$patientId", function () use ($patientId, $month) {
            $this->setPatientId($patientId)
                ->setMonth($month)
                ->setPatientUser()
                ->setPatientDto()
                ->process()
                ->processPatientBillingStatus();
        });
    }

    public function fromDTO(PatientMonthlyBillingDTO $dto): void
    {
        measureTime("ProcessPatientSummaries::fromDTO:{$dto->getPatientId()}", function () use ($dto) {
            $this->setPatientDto($dto)
                ->process()
                ->processPatientBillingStatus();
        });
    }

    //todo: change name - remove wipe
    public static function wipeAndReprocessForMonth(int $patientUserId, Carbon $month): void
    {
        app(self::class)->fromDTO(
            PatientMonthlyBillingDTO::generateFromUser(
                (app(PatientServiceProcessorRepository::class))->getPatientWithBillingDataForMonth($patientUserId, $month),
                $month
            )
        );
    }

    private function process(): self
    {
        if ( ! isset($this->patientDTO) || is_null($this->patientDTO)) {
            sendSlackMessage('#billing_alerts', "Warning! (ProcessPatientSummaries:) Patient({$this->patientId}) Billing Data are invalid. (DTO is null)");

            return $this;
        }
        $this->patientDTO = $this->processor->process($this->patientDTO);

        return $this;
    }

    private function processPatientBillingStatus(): void
    {
        if ( ! isset($this->patientDTO) || is_null($this->patientDTO)) {
            return;
        }

        ProcessPatientBillingStatus::fromDto($this->patientDTO);
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
            sendSlackMessage('#billing_alerts', "Warning! (ProcessPatientSummaries:) Patient ({$this->patientId}) does not exist.");

            return $this;
        }

        if (is_null($this->patientUser->patientInfo->location)) {
            sendSlackMessage('#billing_alerts', "Warning! (ProcessPatientSummaries:) ({$this->patientUser->id}) does not have location attached.");

            return $this;
        }

        $this->patientDTO = PatientMonthlyBillingDTO::generateFromUser($this->patientUser, $this->month);

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
            ->getPatientWithBillingDataForMonth($this->patientId, $this->month);

        return $this;
    }
}
