<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;

class PatientIsOfServiceCode
{
    protected bool $billingRevampIsEnabled;

    protected bool $bypassLocationCheck;

    protected bool $bypassRequiresConsent;
    protected int $patientId;

    protected PatientServiceProcessorRepository $repo;

    protected string $serviceCode;

    public function __construct(int $patientId, string $serviceCode, bool $bypassRequiresConsent = false, bool $bypassLocationCheck = false)
    {
        $this->patientId             = $patientId;
        $this->serviceCode           = $serviceCode;
        $this->bypassRequiresConsent = $bypassRequiresConsent;
        $this->bypassLocationCheck   = $bypassLocationCheck;
    }

    public static function execute(int $patientId, string $serviceCode, $bypassRequiresConsent = false, $bypassLocationCheck = false): bool
    {
        return (new static($patientId, $serviceCode, $bypassRequiresConsent, $bypassLocationCheck))->isOfServiceCode();
    }

    public function isOfServiceCode(): bool
    {
        if ($this->patientHasForcedService()) {
            return true;
        }

        return $this->hasSummary() && $this->hasEnoughProblems() && $this->patientLocationHasService() && ! $this->hasClashingService();
    }

    private function billingRevampIsEnabled(): bool
    {
        return BillingCache::billingRevampIsEnabled();
    }

    private function hasClashingService(): bool
    {
        if ($this->isAClashForForcedService()) {
            return true;
        }
        $clashes = ChargeableService::getClashesWithService($this->serviceCode);

        if (empty($clashes)) {
            return false;
        }

        foreach ($clashes as $clashingService) {
            //todo-investigate: turn into patient has enough problems only check for performance?
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

    private function isAClashForForcedService(): bool
    {
        return $this->repo()
            ->getPatientWithBillingDataForMonth($this->patientId, Carbon::now()->startOfMonth())
            ->forcedChargeableServices
            ->filter(function ($fcs) {
                $clashes = ChargeableService::getClashesWithService($fcs->code);

                return in_array($this->serviceCode, $clashes);
            })
            ->isNotEmpty();
    }

    private function minimumProblemCountForService(): int
    {
        return PatientProblemsForBillingProcessing::SERVICE_PROBLEMS_MIN_COUNT_MAP[ChargeableService::getCodeForPatientProblems($this->serviceCode)] ?? 0;
    }

    private function patientHasForcedService(): bool
    {
        return $this->repo()
            ->getPatientWithBillingDataForMonth($this->patientId, Carbon::now()->startOfMonth())
            ->forcedChargeableServices
            //todo:make sure this does not pass for past months
            ->where('code', $this->serviceCode)
            ->isNotEmpty();
    }

    private function patientLocationHasService(): bool
    {
        if ($this->bypassLocationCheck) {
            return true;
        }

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
