<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\ValueObjects\AttestationRequirementsDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;

class AttestationRequirements
{
    protected AttestationRequirementsDTO $dto;
    protected User $patient;
    protected PatientMonthlyBillingDTO $patientDto;

    public function __construct(User $patient)
    {
        $this->patient = $patient;
        $this->dto     = new AttestationRequirementsDTO();
    }

    public function execute(): AttestationRequirementsDTO
    {
        return $this->billingRevampIsEnabled() ? $this->getRequirements() : $this->getLegacyAttestationRequirements();
    }

    public static function get(User $patient): AttestationRequirementsDTO
    {
        return (new static($patient))->execute();
    }

    private function attestedBhiProblemsCount(): self
    {
        $bhiProblems = collect($this->patientDto->getPatientProblems())->filter(fn (PatientProblemForProcessing $p) => ChargeableService::BHI === $p->getCode());
        $attested    = $this->patient->attestedProblems->pluck('ccd_problem_id')->toArray();

        $this->dto->setAttestedBhiProblemsCount(
            $bhiProblems->filter(
                fn (PatientProblemForProcessing $p) => in_array($p->getId(), $attested)
            )
                ->count()
        );

        return $this;
    }

    private function attestedCcmProblemsCount(): self
    {
        $ccmProblems = collect($this->patientDto->getPatientProblems())->filter(fn (PatientProblemForProcessing $p) => ChargeableService::CCM === $p->getCode());
        $attested    = $this->patient->attestedProblems->pluck('ccd_problem_id')->toArray();

        $this->dto->setAttestedCcmProblemsCount(
            $ccmProblems->filter(
                fn (PatientProblemForProcessing $p) => in_array($p->getId(), $attested)
            )
                ->count()
        );

        return $this;
    }

    private function billingRevampIsEnabled(): bool
    {
        return BillingCache::billingRevampIsEnabled();
    }

    private function getDto(): AttestationRequirementsDTO
    {
        return $this->dto;
    }

    private function getLegacyAttestationRequirements()
    {
        $this->setPatientDto();
        $this->isEnabled();

        if ( ! PatientMonthlySummary::existsForCurrentMonthForPatient($this->patient)) {
            PatientMonthlySummary::createFromPatient($this->patient->id, Carbon::now()->startOfMonth());
        }

        /**
         * @var PatientMonthlySummary
         */
        $pms = $this->patient->patientSummaries()
            ->with([
                'allChargeableServices',
                'attestedProblems',
            ])
            ->getCurrent()
            ->first();

        if ($pms->allchargeableServices->isEmpty()) {
            $pms->attachChargeableServicesToFulfill();
        }

        $this->setHasCcm()->setHasPcm()->setHasRpm();

        $this->dto->setAttestedCcmProblemsCount($pms->ccmAttestedProblems(true)->count());
        $this->dto->setAttestedBhiProblemsCount($pms->bhiAttestedProblems(true)->count());

        return $this->dto;
    }

    private function getRequirements(): AttestationRequirementsDTO
    {
        return $this->setPatientDto()
            ->isEnabled()
            ->setHasCcm()
            ->setHasPcm()
            ->setHasRpm()
            ->attestedCcmProblemsCount()
            ->attestedBhiProblemsCount()
            ->getDto();
    }

    private function isEnabled(): self
    {
        $this->dto->setDisabled(
            ! complexAttestationRequirementsEnabledForPractice($this->patient->primaryPractice->id)
        );

        return $this;
    }

    private function setHasCcm(): self
    {
        $this->dto->setHasCcm(
            PatientIsOfServiceCode::fromDTO($this->patientDto, ChargeableService::CCM) ||
            PatientIsOfServiceCode::fromDTO($this->patientDto, ChargeableService::GENERAL_CARE_MANAGEMENT)
        );

        return $this;
    }

    private function setHasPcm(): self
    {
        $this->dto->setHasPcm(
            PatientIsOfServiceCode::fromDTO($this->patientDto, ChargeableService::PCM)
        );

        return $this;
    }

    private function setHasRpm(): self
    {
        $this->dto->setHasRpm(
            PatientIsOfServiceCode::fromDTO($this->patientDto, ChargeableService::RPM)
        );

        return $this;
    }

    private function setPatientDto(): self
    {
        $this->patientDto = PatientMonthlyBillingDTO::generateFromUser($this->patient, Carbon::now()->startOfMonth());
        return $this;
    }
}
