<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Algorithms;

use CircleLinkHealth\NurseInvoices\ValueObjects\PatientPayCalculationResult;

class LegacyPaymentAlgorithm extends NursePaymentAlgorithm
{
    public function calculate(): PatientPayCalculationResult
    {
        $towardsCCm = $this->patientCareRateLogs
            ->where('nurse_id', '=', $this->nurseInfo->id)
            ->where('ccm_type', '=', 'accrued_towards_ccm')
            ->sum('increment');
        $towardsCCm = $towardsCCm / self::HOUR_IN_SECONDS;

        $afterCCm = $this->patientCareRateLogs
            ->where('nurse_id', '=', $this->nurseInfo->id)
            ->where('ccm_type', '=', 'accrued_after_ccm')
            ->sum('increment');
        $afterCCm = $afterCCm / self::HOUR_IN_SECONDS;

        $highRates = collect([$towardsCCm * $this->nurseInfo->high_rate]);
        $lowRates  = collect([$afterCCm * $this->nurseInfo->low_rate]);

        return PatientPayCalculationResult::withHighLowRates($highRates, $lowRates);
    }
}
