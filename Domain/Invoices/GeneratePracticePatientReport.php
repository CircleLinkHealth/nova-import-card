<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Invoices;

use CircleLinkHealth\CcmBilling\Domain\Patient\PatientProblemsForBillingProcessing;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PracticePatientReportData;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Support\Collection;

class GeneratePracticePatientReport
{
    private PatientMonthlyBillingStatus $billingStatus;

    public function execute(): PracticePatientReportData
    {
        $patient              = $this->billingStatus->patientUser;
        $result               = new PracticePatientReportData();
        $result->ccmTime      = $this->roundToMinutes($this->getAllTimeExceptBhi());
        $result->bhiTime      = $this->roundToMinutes($this->getBhiTime());
        $result->name         = $patient->getFullName();
        $result->dob          = $patient->getBirthDate();
        $result->practice     = $patient->program_id;
        $result->provider     = $patient->getBillingProviderName();
        $result->billingCodes = $this->getBillingCodes();
        $result->locationName = $patient->getPreferredLocationName();

        $this->setCodes($result);

        return $result;
    }

    public function setBillingStatus(PatientMonthlyBillingStatus $billingStatus): GeneratePracticePatientReport
    {
        $this->billingStatus = $billingStatus;

        return $this;
    }

    private function formatProblemCodesForReport(Collection $problems)
    {
        if ($problems->isEmpty()) {
            return 'N/A';
        }

        return $problems
            ->map(fn (PatientProblemForProcessing $problem) => $problem->getCode())
            ->filter()
            ->unique()
            ->implode(', ');
    }

    private function getAllTimeExceptBhi(): int
    {
        return $this->billingStatus->patientUser->chargeableMonthlySummariesView
            ->where('chargeable_service_code', '!=', ChargeableService::BHI)
            ->sum('total_time');
    }

    private function getBhiTime(): int
    {
        return $this->billingStatus->patientUser->chargeableMonthlySummariesView
            ->where('chargeable_service_code', '=', ChargeableService::BHI)
            ->sum('total_time');
    }

    private function getBillingCodes(): string
    {
        return $this->billingStatus->patientUser->chargeableMonthlySummariesView
            ->where('is_fulfilled', '=', 1)
            ->implode('chargeable_service_code', ', ');
    }

    private function hasServiceCode(string $code): bool
    {
        return $this->billingStatus->patientUser->chargeableMonthlySummariesView
            ->where('is_fulfilled', '=', 1)
            ->where('chargeable_service_code', '=', $code)
            ->isNotEmpty();
    }

    private function roundToMinutes(int $seconds)
    {
        return round($seconds / 60, 2);
    }

    private function setCodes(PracticePatientReportData $result)
    {
        $attested = $this->billingStatus->patientUser->attestedProblems->pluck('ccd_problem_id')->toArray();

        $ccmProblems                = PatientProblemsForBillingProcessing::getForCodes($this->billingStatus->patient_user_id, [ChargeableService::CCM]);
        $ccmProblemCodes            = $this->hasServiceCode(ChargeableService::RPM) ? $ccmProblems : $ccmProblems->filter(fn (PatientProblemForProcessing $p) => in_array($p->getId(), $attested));
        $result->ccmProblemCodes    = $this->formatProblemCodesForReport($ccmProblemCodes);
        $result->allCcmProblemCodes = $this->formatProblemCodesForReport($ccmProblems);

        $bhiProblems         = PatientProblemsForBillingProcessing::getForCodes($this->billingStatus->patient_user_id, [ChargeableService::BHI]);
        $bhiProblemCodes     = $this->hasServiceCode(ChargeableService::BHI) ? $bhiProblems->filter(fn (PatientProblemForProcessing $p) => in_array($p->getId(), $attested)) : collect();
        $result->bhiCodes    = $this->formatProblemCodesForReport($bhiProblemCodes);
        $result->allBhiCodes = $this->formatProblemCodesForReport($bhiProblems);
    }
}
