<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;

class PatientIsOfServiceCode
{
    protected bool $billingRevampIsEnabled;

    protected bool $bypassRequiresConsent;
    protected int $patientId;

    protected PatientServiceProcessorRepository $repo;

    protected string $serviceCode;

    public function __construct(int $patientId, string $serviceCode, bool $bypassRequiresConsent = false)
    {
        $this->patientId             = $patientId;
        $this->serviceCode           = $serviceCode;
        $this->bypassRequiresConsent = $bypassRequiresConsent;
    }

    public static function execute(int $patientId, string $serviceCode, $bypassRequiresConsent = false): bool
    {
        return (new static($patientId, $serviceCode, $bypassRequiresConsent))->isOfServiceCode();
    }
    
    public static function excludeLocationCheck(int $patientId, string $serviceCode, $bypassRequiresConsent = false): bool
    {
        return (new static($patientId, $serviceCode, $bypassRequiresConsent))->isOfServiceCodeExcludingLocationCheck();
    }

    public function isOfServiceCode(): bool
    {
        return $this->hasSummary() && $this->hasEnoughProblems() && $this->patientLocationHasService() && ! $this->hasClashingService();
    }
    
    public function isOfServiceCodeExcludingLocationCheck(): bool
    {
        return $this->hasSummary() && $this->hasEnoughProblems() && ! $this->hasClashingService();
    }

    private function billingRevampIsEnabled(): bool
    {
        if ( ! isset($this->billingRevampIsEnabled)) {
            $this->billingRevampIsEnabled = Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG);
        }

        return $this->billingRevampIsEnabled;
    }

    private function hasClashingService(): bool
    {
        $clashes = ChargeableService::getClashesWithService($this->serviceCode);

        if (empty($clashes)) {
            return false;
        }

        foreach ($clashes as $clashingService) {
            if (PatientIsOfServiceCode::execute($this->patientId, $clashingService)) {
                return true;
            }
        }

        return false;
    }

    private function hasEnoughProblems(): bool
    {
        return $this->problemsOfServiceCount() >= $this->minimumProblemCountForService();
    }

    private function hasSummary(): bool
    {
        if ( ! $this->billingRevampIsEnabled()) {
            if ( ! $this->bypassRequiresConsent && ChargeableService::BHI === $this->serviceCode) {
                return ! $this->requiresPatientBhiConsent();
            }

            return true;
        }

        return $this->repo()->getChargeablePatientSummaries($this->patientId, Carbon::now()->startOfMonth())
            ->where('chargeable_service_code', $this->serviceCode)
            ->where('requires_patient_consent', $this->bypassRequiresConsent)
            ->count() > 0;
    }

    private function minimumProblemCountForService(): int
    {
        return PatientProblemsForBillingProcessing::SERVICE_PROBLEMS_MIN_COUNT_MAP[ChargeableService::getCodeForPatientProblems($this->serviceCode)] ?? 0;
    }

    private function patientLocationHasService(): bool
    {
        $patient = $this->repo()->getPatientWithBillingDataForMonth($this->patientId, $thisMonth = Carbon::now()->startOfMonth());

        if ( ! $this->billingRevampIsEnabled()) {
            return $patient->primaryPractice->hasServiceCode($this->serviceCode);
        }

        return $patient->patientInfo->location->chargeableServiceSummaries
            ->where('chargeableService.code', $this->serviceCode)
            ->where('chargeable_month', $thisMonth)
            ->isNotEmpty();
    }

    private function problemsOfServiceCount(): int
    {
        return PatientProblemsForBillingProcessing::getForCodes($this->patientId, [ChargeableService::getCodeForPatientProblems($this->serviceCode)])->count();
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }

    private function requiresPatientBhiConsent(): bool
    {
        return ! User::hasBhiConsent()
            ->whereId($this->patientId)
            ->exists();
    }
}
