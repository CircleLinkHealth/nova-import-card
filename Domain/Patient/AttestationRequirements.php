<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\AttestationRequirementsDTO;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;

class AttestationRequirements
{
    protected AttestationRequirementsDTO $dto;
    protected User $patient;
    protected int $patientId;
    protected PatientServiceProcessorRepository $repo;

    public function __construct(int $patientId)
    {
        $this->patientId = $patientId;
        $this->dto       = new AttestationRequirementsDTO();
    }

    public function execute(): AttestationRequirementsDTO
    {
        return $this->setPatient()
            ->isEnabled()
            ->hasCcm()
            ->hasPcm()
            ->attestedCcmProblemsCount()
            ->attestedBhiProblemsCount()
            ->getDto();
    }

    public static function get(int $patientId): AttestationRequirementsDTO
    {
        return (new static($patientId))->execute();
    }

    private function attestedBhiProblemsCount(): self
    {
        $bhiProblems = $this->repo()->patientProblemsOfServiceCode($this->patientId, ChargeableService::BHI);

        $this->dto->setAttestedBhiProblemsCount(
            $bhiProblems->whereIn(
                'id',
                $this->patient->attestedProblems->pluck('ccd_problem_id')->toArray()
            )->count()
        );

        return $this;
    }

    private function attestedCcmProblemsCount(): self
    {
        $ccmProblems = $this->repo()->patientProblemsOfServiceCode($this->patientId, ChargeableService::CCM);

        $this->dto->setAttestedCcmProblemsCount(
            $ccmProblems->whereIn(
                'id',
                $this->patient->attestedProblems->pluck('ccd_problem_id')->toArray()
            )->count()
        );

        return $this;
    }

    private function getDto(): AttestationRequirementsDTO
    {
        return $this->dto;
    }

    private function hasCcm(): self
    {
        $this->dto->setHasCcm(
            PatientIsOfServiceCode::execute($this->patientId, ChargeableService::CCM)
        );

        return $this;
    }

    private function hasPcm(): self
    {
        $this->dto->setHasPcm(
            PatientIsOfServiceCode::execute($this->patientId, ChargeableService::PCM)
        );

        return $this;
    }

    private function isEnabled(): self
    {
        $this->dto->setDisabled(
            ! complexAttestationRequirementsEnabledForPractice($this->patient->primaryPractice->id)
        );

        return $this;
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }

    private function setPatient(): self
    {
        $this->patient = $this->repo()
            ->getPatientWithBillingDataForMonth(
                $this->patientId,
                Carbon::now()->startOfMonth()
            );

        return $this;
    }
}
