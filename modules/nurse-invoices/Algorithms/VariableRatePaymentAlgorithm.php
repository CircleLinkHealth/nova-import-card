<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Algorithms;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\NurseInvoices\ValueObjects\PatientPayCalculationResult;
use CircleLinkHealth\NurseInvoices\ValueObjects\TimeRangeEntry;
use CircleLinkHealth\NurseInvoices\ValueObjects\VariableRatePay;
use Illuminate\Support\Collection;

class VariableRatePaymentAlgorithm extends NursePaymentAlgorithm
{
    /**
     * If practice has CCM Plus:
     * 1. High rate 1 for 0-20 range [ccm or bhi].
     * 2. High rate 2 for 20-40 range only if completed + have at least one successful call in any range [only ccm].
     * 3. High rate 3 for 40-60 range only if completed + have at least one successful call in any range [only ccm].
     * 4. Low rate otherwise [ccm or bhi].
     *
     * Else:
     * 1. High rate 1 for 0-20 range [ccm or bhi].
     * 2. Low rate for the rest [ccm or bhi].
     *
     * @param $totalCcm
     * @param $totalBhi
     * @param $ranges
     */
    public function calculate(): PatientPayCalculationResult
    {
        $this->practiceHasCcmPlus                 = $this->practiceHasCcmPlusCode($this->patient->primaryPractice);
        $timeEntryPerCsCodePerRangePerNurseInfoId = $this->separateTimeAccruedInRanges($this->patientCareRateLogs);

        $highRates = collect();
        $lowRates  = collect();

        $nurseInfoId = $this->nurseInfo->id;

        $rangesForNurseOnly = $timeEntryPerCsCodePerRangePerNurseInfoId
            ->map(function (Collection $timeEntryForCsCodePerRangePerNurseInfoId) use ($nurseInfoId) {
                return $timeEntryForCsCodePerRangePerNurseInfoId
                    ->map(function (Collection $timeEntryForCsCodeForRangePerNurseInfoId) use ($nurseInfoId) {
                        return $timeEntryForCsCodeForRangePerNurseInfoId->has($nurseInfoId)
                            ? $timeEntryForCsCodeForRangePerNurseInfoId->get($nurseInfoId)
                            : collect();
                    })
                    ->filter();
            })
            ->filter();

        $hasSuccessfulCall = $this->patientHasAtLeastOneSuccessfulCall($rangesForNurseOnly);

        $rangesForNurseOnly->each(function (Collection $timeEntryForCsCodePerRange, string $csCode) use ($hasSuccessfulCall, $lowRates, $highRates) {
            if ($timeEntryForCsCodePerRange->isEmpty()) {
                return;
            }

            //pcm not supported for this algo
            if (ChargeableService::PCM === $csCode) {
                return;
            }

            $totalTime = $this->getTotalTimeForMonth($csCode);
            $timeEntryForCsCodePerRange->each(function (TimeRangeEntry $entry, int $rangeKey) use ($csCode, $hasSuccessfulCall, $lowRates, $highRates, $totalTime) {
                $pay = $this->getVariableRatePayForRange(
                    $hasSuccessfulCall,
                    $totalTime,
                    $csCode,
                    $rangeKey,
                    $entry
                );
                if ( ! $pay) {
                    return;
                }
                if ($pay->rate === $this->nurseInfo->low_rate) {
                    $lowRates->push($pay->pay);
                } else {
                    $highRates->push($pay->pay);
                }
            });
        });

        return PatientPayCalculationResult::withHighLowRates($highRates, $lowRates);
    }

    private function getVariableRatePayForRange(
        bool $hasSuccessfulCall,
        int $totalTime,
        string $csCode,
        int $rangeKey,
        TimeRangeEntry $range
    ): ?VariableRatePay {
        if (empty($range->duration)) {
            return null;
        }

        $rate = $this->nurseInfo->low_rate;

        switch ($rangeKey) {
        case 0:
            //0-20 always pays high rate
            $rate = $this->nurseInfo->high_rate;
            break;
        case 1:
            if (in_array($csCode, [ChargeableService::CCM, ChargeableService::RPM]) && $hasSuccessfulCall && $this->practiceHasCcmPlus &&
                $totalTime >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
                $rate = $this->nurseInfo->high_rate_2;
            }
            break;
        case 2:
            if (in_array($csCode, [ChargeableService::CCM, ChargeableService::RPM]) && $hasSuccessfulCall && $this->practiceHasCcmPlus &&
                $totalTime >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS) {
                $rate = $this->nurseInfo->high_rate_3;
            }
            break;
        default:
            break;
    }

        $nurseCcmInRange = $range->duration / self::HOUR_IN_SECONDS;

        return new VariableRatePay($nurseCcmInRange * $rate, $rate);
    }

    private function patientHasAtLeastOneSuccessfulCall(Collection $timeEntryPerCsCodePerRangeForNurse)
    {
        $hasSuccessfulCall = false;
        $timeEntryPerCsCodePerRangeForNurse->each(function (Collection $timeEntryForCsCodePerRange) use (&$hasSuccessfulCall) {
            $hasSuccessfulCall = $timeEntryForCsCodePerRange
                ->filter(function (TimeRangeEntry $entry) {
                    return $entry->hasSuccessfulCall;
                })
                ->isNotEmpty();
            if ($hasSuccessfulCall) {
                //exit loop
                return false;
            }
        });

        return $hasSuccessfulCall;
    }
}
