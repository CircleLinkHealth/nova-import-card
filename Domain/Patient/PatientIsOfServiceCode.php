<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\Customer\AppConfig\PracticesRequiringSpecialBhiConsent;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;

class PatientIsOfServiceCode
{
    protected int $patientId;

    protected PatientServiceProcessorRepository $repo;

    protected bool $bypassRequiresConsent;

    protected string $serviceCode;
    
    protected bool $billingRevampIsEnabled;

    public function __construct(int $patientId, string $serviceCode, bool $bypassRequiresConsent = false)
    {
        $this->patientId       = $patientId;
        $this->serviceCode     = $serviceCode;
        $this->bypassRequiresConsent = $bypassRequiresConsent;
    }

    public static function execute(int $patientId, string $serviceCode, $bypassRequiresConsent = false): bool
    {
        return (new static($patientId, $serviceCode, $bypassRequiresConsent))->isOfServiceCode();
    }

    public function isOfServiceCode(): bool
    {
        return $this->hasSummary() && $this->hasEnoughProblems() && $this->patientLocationHasService();
    }

    private function hasEnoughProblems(): bool
    {
        return $this->problemsOfServiceCount() >= $this->minimumProblemCountForService();
    }
    
    private function billingRevampIsEnabled():bool
    {
        if(! isset($this->billingRevampIsEnabled)){
            $this->billingRevampIsEnabled = Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG);
        }
        
        return $this->billingRevampIsEnabled;
    }

    private function hasSummary(): bool
    {
        if (! $this->billingRevampIsEnabled()){
            if (! $this->bypassRequiresConsent && $this->serviceCode === ChargeableService::BHI){
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
        return $this->patientRequiredProblemsCountMap()[$this->serviceCode] ?? 0;
    }

    private function patientRequiredProblemsCountMap(): array
    {
        return [
            ChargeableService::CCM => 2,
            ChargeableService::BHI => 1,
            ChargeableService::PCM => 1,
            ChargeableService::RPM => 1
        ];
    }

    private function problemsOfServiceCount(): int
    {
        return PatientProblemsForBillingProcessing::getForCodes($this->patientId, [$this->serviceCode])->count();
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }
    
    private function requiresPatientBhiConsent():bool
    {
        return ! User::hasBhiConsent()
            ->whereId($this->patientId)
            ->exists();
    }
    
    private function patientLocationHasService():bool
    {
        $patient = $this->repo()->getPatientWithBillingDataForMonth($this->patientId, $thisMonth = Carbon::now()->startOfMonth());
    
        if (! $this->billingRevampIsEnabled()){
            return $patient->primaryPractice->hasServiceCode($this->serviceCode);
        }
        
        return $patient->patientInfo->location->chargeableServiceSummaries
            ->where('code', $this->serviceCode)
            ->where('chargeable_month', $thisMonth)
            ->isNotEmpty();
    }
}
