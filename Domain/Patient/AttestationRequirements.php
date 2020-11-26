<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\CcmBilling\ValueObjects\AttestationRequirementsDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;

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
        return $this->billingRevampIsEnabled() ? $this->getRequirements() : $this->getLegacyAttestationRequirements();
    }

    public static function get(int $patientId): AttestationRequirementsDTO
    {
        return (new static($patientId))->execute();
    }

    private function attestedBhiProblemsCount(): self
    {
        $bhiProblems = PatientProblemsForBillingProcessing::getForCodes($this->patientId, [ChargeableService::BHI]);
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
        $ccmProblems = PatientProblemsForBillingProcessing::getForCodes($this->patientId, [ChargeableService::CCM]);
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
        return Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG);
    }

    private function getDto(): AttestationRequirementsDTO
    {
        return $this->dto;
    }

    private function getLegacyAttestationRequirements()
    {
        $this->setPatient();
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
        return $this->setPatient()
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

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }

    private function setHasCcm(): self
    {
        $this->dto->setHasCcm(
            PatientIsOfServiceCode::execute($this->patientId, ChargeableService::CCM) ||
            PatientIsOfServiceCode::execute($this->patientId, ChargeableService::GENERAL_CARE_MANAGEMENT)
        );

        return $this;
    }

    private function setHasPcm(): self
    {
        $this->dto->setHasPcm(
            PatientIsOfServiceCode::execute($this->patientId, ChargeableService::PCM)
        );

        return $this;
    }

    private function setHasRpm(): self
    {
        $this->dto->setHasRpm(
            PatientIsOfServiceCode::execute($this->patientId, ChargeableService::RPM)
        );

        return $this;
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
