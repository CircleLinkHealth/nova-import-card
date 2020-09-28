<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\SharedModels\Entities\Problem;

class PatientIsOfServiceCode
{
    protected int $patientId;

    protected bool $requiresConsent;

    protected string $serviceCode;

    public function __construct(int $patientId, string $serviceCode, bool $requiresConsent = false)
    {
        $this->patientId       = $patientId;
        $this->serviceCode     = $serviceCode;
        $this->requiresConsent = $requiresConsent;
    }

    public static function execute(int $patientId, string $serviceCode, $requiresConsent = false): bool
    {
        return (new static($patientId, $serviceCode, $requiresConsent))->isOfServiceCode();
    }

    public function isOfServiceCode(): bool
    {
        return $this->hasSummary() && $this->hasEnoughProblems();
    }

    private function hasEnoughProblems(): bool
    {
        return  $this->problemsOfServiceCount() >= $this->minimumProblemCountForService();
    }

    private function hasSummary(): bool
    {
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $this->patientId)
            ->where('chargeable_service_code', $this->serviceCode)
            ->where('chargeable_month', Carbon::now()->startOfMonth())
            ->where('requires_patient_consent', $this->requiresConsent)
            ->exists();
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
        ];
    }

    private function problemsOfServiceCount(): int
    {
        return Problem::wherePatientId($this->patientId)
            ->isBillable()
            ->ofService($this->serviceCode)
            ->count();
    }
}
