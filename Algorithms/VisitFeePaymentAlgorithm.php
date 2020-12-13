<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Algorithms;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\NurseInvoices\ValueObjects\PatientPayCalculationResult;
use CircleLinkHealth\NurseInvoices\ValueObjects\TimeRangeEntry;
use CircleLinkHealth\NurseInvoices\ValueObjects\VisitFeePay;
use Illuminate\Support\Collection;

class VisitFeePaymentAlgorithm extends NursePaymentAlgorithm
{
    public function calculate(): PatientPayCalculationResult
    {
        $this->practiceHasCcmPlus                 = $this->practiceHasCcmPlusCode($this->patient->primaryPractice);
        $timeEntryPerCsCodePerRangePerNurseInfoId = $this->measureTimeAndLog(
            'separateTimeAccruedInRanges',
            fn () => $this->separateTimeAccruedInRanges($this->patientCareRateLogs)
        );

        $visitsPerChargeableServiceCodePerDay = collect();

        $patientHasAtLeastOneSuccessfulCall = $this->patientHasAtLeastOneSuccessfulCall($timeEntryPerCsCodePerRangePerNurseInfoId);
        if ( ! $patientHasAtLeastOneSuccessfulCall) {
            return PatientPayCalculationResult::withVisits($visitsPerChargeableServiceCodePerDay);
        }

        // CPM-1997
        // If only 1 billable event, the RN(s) with successful call(s) split VF proportionally,
        // any other RNs spending time without a successful call get 0%.
        $singleBillableEvent = $this->getBillableEventIfOnlyOne($timeEntryPerCsCodePerRangePerNurseInfoId);

        if ($singleBillableEvent) {
            $this->measureTimeAndLog(
                'getVisitFeePayForOneBillableEvent',
                function () use (
                    $visitsPerChargeableServiceCodePerDay,
                    $timeEntryPerCsCodePerRangePerNurseInfoId,
                    $singleBillableEvent
                ) {
                    $pay = $this->getVisitFeePayForOneBillableEvent(
                        $singleBillableEvent,
                        $timeEntryPerCsCodePerRangePerNurseInfoId->get($singleBillableEvent, collect())
                    );
                    $date = $pay->lastLogDate;
                    if ($date) {
                        /** @var Collection $visitsOfChargeableServiceCodePerDay */
                        $visitsOfChargeableServiceCodePerDay = $visitsPerChargeableServiceCodePerDay->get($singleBillableEvent, collect());
                        /** @var VisitFeePay $visitsForDate */
                        $visitsForDate = $visitsOfChargeableServiceCodePerDay->get($date, new VisitFeePay(null, 0, 0));
                        $visitsOfChargeableServiceCodePerDay->put($date, new VisitFeePay(
                            null,
                            $visitsForDate->fee + $pay->fee,
                            $visitsForDate->count + $pay->count
                        ));
                        $visitsPerChargeableServiceCodePerDay->put($singleBillableEvent, $visitsOfChargeableServiceCodePerDay);
                    }
                }
            );
        } else {
            $this->measureTimeAndLog(
                'getVisitFeePayForRange',
                function () use (
                    $timeEntryPerCsCodePerRangePerNurseInfoId,
                    $visitsPerChargeableServiceCodePerDay
                ) {
                    $timeEntryPerCsCodePerRangePerNurseInfoId->each(function (Collection $timeEntryForCsCodePerRangePerNurseInfoId, string $csCode) use ($visitsPerChargeableServiceCodePerDay) {
                        $totalTime = $this->getTotalTimeForMonth($csCode);
                        $timeEntryForCsCodePerRangePerNurseInfoId->each(function (Collection $timeEntryForCsCodeForRangePerNurseInfoId, int $rangeKey) use ($csCode, $totalTime, $visitsPerChargeableServiceCodePerDay) {
                            if ($timeEntryForCsCodeForRangePerNurseInfoId->isEmpty()) {
                                return;
                            }

                            $pay = $this->getVisitFeePayForRange(
                                $csCode,
                                $totalTime,
                                $rangeKey,
                                $timeEntryForCsCodeForRangePerNurseInfoId
                            );

                            if ($pay && $pay->lastLogDate) {
                                /** @var Collection $visitsOfChargeableServiceCodePerDay */
                                $visitsOfChargeableServiceCodePerDay = $visitsPerChargeableServiceCodePerDay->get($csCode, collect());
                                /** @var VisitFeePay $visitsForDate */
                                $visitsForDate = $visitsOfChargeableServiceCodePerDay->get($pay->lastLogDate, new VisitFeePay(null, 0, 0));
                                $visitsOfChargeableServiceCodePerDay->put($pay->lastLogDate, new VisitFeePay(
                                    null,
                                    $visitsForDate->fee + $pay->fee,
                                    $visitsForDate->count + $pay->count
                                ));
                                $visitsPerChargeableServiceCodePerDay->put($csCode, $visitsOfChargeableServiceCodePerDay);
                            }
                        });
                    });
                }
            );
        }

        return PatientPayCalculationResult::withVisits($visitsPerChargeableServiceCodePerDay);
    }

    private function getBillableEventIfOnlyOne(Collection $timeEntryPerCsCodePerRangePerNurseInfoId)
    {
        $noOfBillableEvents = 0;
        /** @var ?string $lastBillableCode */
        $lastBillableCode = null;
        $timeEntryPerCsCodePerRangePerNurseInfoId
            ->keys()
            ->each(function ($csCode) use (&$noOfBillableEvents, &$lastBillableCode) {
                $totalTimeForMonth = $this->getTotalTimeForMonth($csCode);
                switch ($csCode) {
                    case ChargeableService::GENERAL_CARE_MANAGEMENT:
                    case ChargeableService::CCM:
                    case ChargeableService::RPM:
                    case ChargeableService::BHI:
                        if ($totalTimeForMonth >= self::MONTHLY_TIME_TARGET_IN_SECONDS) {
                            ++$noOfBillableEvents;
                            $lastBillableCode = $csCode;
                        }
                        if ($totalTimeForMonth >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS
                            && (($this->practiceHasCcmPlus && ChargeableService::CCM === $csCode) || ChargeableService::RPM === $csCode)) {
                            ++$noOfBillableEvents;
                            $lastBillableCode = $csCode;
                        }
                        if ($totalTimeForMonth >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS
                            && (($this->practiceHasCcmPlus && ChargeableService::CCM === $csCode) || ChargeableService::RPM === $csCode)) {
                            ++$noOfBillableEvents;
                            $lastBillableCode = $csCode;
                        }
                        break;
                    case ChargeableService::PCM:
                        if ($totalTimeForMonth >= self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM) {
                            ++$noOfBillableEvents;
                            $lastBillableCode = $csCode;
                        }
                        break;
                }
            });

        return 1 === $noOfBillableEvents ? $lastBillableCode : null;
    }

    /**
     * Get percentage of allocation of nurse in a specific range.
     * Used only for the CCM Plus Alternate Algorithm (VISIT FEE based).
     *
     * 1 RN for the range -> 100%
     * 2 RNs for the range ->
     *          both succesful call -> 50% / 50%
     *          none with successful call -> 50% / 50%
     *          only one with successful call -> 100% / 0%
     *
     * @param $nurseInfoId
     * @param $range
     * @param bool $isBehavioral
     */
    private function getNurseTimePercentageAllocationInRange(
        int $nurseInfoId,
        string $csCode,
        Collection $timeEntryForCsCodeForRangePerNurseInfoId
    ): float {
        if ( ! $timeEntryForCsCodeForRangePerNurseInfoId->has($nurseInfoId)) {
            return 0;
        }

        //only 1 RN, pay the full VF, regardless of calls
        if (1 === $timeEntryForCsCodeForRangePerNurseInfoId->count()) {
            return 1;
        }

        /** @var Collection|TimeRangeEntry[] $filtered */
        $filtered = $timeEntryForCsCodeForRangePerNurseInfoId->filter(function (TimeRangeEntry $entry) {
            return $entry->hasSuccessfulCall;
        });

        //none of them had successful calls
        //or all RNs had successful calls, split the VF proportionally
        if ($filtered->isEmpty() || $timeEntryForCsCodeForRangePerNurseInfoId->count() === $filtered->count()) {
            $target = ChargeableService::PCM === $csCode
                ? self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM
                : self::MONTHLY_TIME_TARGET_IN_SECONDS;

            return $timeEntryForCsCodeForRangePerNurseInfoId->get($nurseInfoId)->duration / $target;
        }

        if ( ! $filtered->has($nurseInfoId)) {
            return 0;
        }

        //if we reach here, it means that some RNs had successful calls in this range and some not
        //we will have to build their proportional VF
        //example 1: RN1: 5 minutes / no successful call | RN2: 15 minutes / successful call => RN2 gets 100% of VF
        //example 2: RN1: 5 minutes / no successful call |
        //           RN2: 10 minutes / successful call |
        //           RN3: 5 minutes / successful call
        //               => RN2: 10/15 * VF | RN3: 5/15 * VF
        $sumOfAllWithCall = $filtered->sum(fn (TimeRangeEntry $entry) => $entry->duration);

        return $filtered->get($nurseInfoId)->duration / $sumOfAllWithCall;
    }

    private function getVisitFeePayForOneBillableEvent(
        string $chargeableServiceCode,
        Collection $timeEntryForCsCodePerRangePerNurseInfoId
    ): VisitFeePay {
        /** @var Collection|TimeRangeEntry[] $nursesTimes */
        $nursesTimes = collect();

        // calculate time for each nurse that has a successful call
        $timeEntryForCsCodePerRangePerNurseInfoId->each(function (Collection $timeEntryForCsCodeForRangePerNurseInfoId) use ($nursesTimes) {
            // in case of ccm and only one billable event, it means that we are paying for
            // the first 20 minute ccm range.
            // so last_log_date must be the date when the 20 minute range was reached
            // i.e. take the first last_log_date, instead of the last
            // (which could be when the 60 minute range was reached)

            return $timeEntryForCsCodeForRangePerNurseInfoId->each(function (TimeRangeEntry $entry, string $nurseInfoId) use ($nursesTimes) {
                /** @var TimeRangeEntry $current */
                $current = $nursesTimes->get($nurseInfoId, new TimeRangeEntry(0, false, null));
                $nursesTimes->put($nurseInfoId, new TimeRangeEntry(
                    $current->duration + $entry->duration,
                    $current->hasSuccessfulCall || $entry->hasSuccessfulCall,
                    $current->lastLogDate ?? $entry->lastLogDate
                ));
            });
        });

        $sumOfAllTime = 0;
        $nursesTimes  = $nursesTimes->filter(function (TimeRangeEntry $entry) use (&$sumOfAllTime) {
            $val = $entry->hasSuccessfulCall;

            if ($val) {
                $sumOfAllTime += $entry->duration;
            }

            return $val;
        });

        if (0 === $sumOfAllTime) {
            $nurseUserId = $this->nurseInfo->user->id;
            sendSlackMessage(
                '#nurse-invoices-alerts',
                "Warning: Will not pay care coach [$nurseUserId] for time tracked on patient [$this->patientId] because I could not find successful call in single billable event [$chargeableServiceCode]"
            );

            return new VisitFeePay(null, 0, 0);
        }

        /** @var TimeRangeEntry $nurseEntry */
        $nurseEntry = $nursesTimes->get(
            $this->nurseInfo->id,
            new TimeRangeEntry(0, false, null)
        );

        $count = ($nurseEntry->duration / $sumOfAllTime);

        return new VisitFeePay(
            $nurseEntry->lastLogDate ?? null,
            $count * $this->nurseInfo->visit_fee,
            $count
        );
    }

    private function getVisitFeePayForRange(
        string $csCode,
        int $totalTime,
        int $rangeKey,
        Collection $timeEntryForCsCodeForRangePerNurseInfoId
    ): ?VisitFeePay {
        $rate = 0.0;

        switch ($csCode) {
            case ChargeableService::GENERAL_CARE_MANAGEMENT:
            case ChargeableService::CCM:
            case ChargeableService::BHI:
            case ChargeableService::RPM:
                switch ($rangeKey) {
                    case 0:
                        if ($totalTime >= self::MONTHLY_TIME_TARGET_IN_SECONDS) {
                            $rate = $this->nurseInfo->visit_fee;
                        }
                        break;
                    case 1:
                        if ($totalTime >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS
                            && (($this->practiceHasCcmPlus && ChargeableService::CCM === $csCode) || ChargeableService::RPM === $csCode)) {
                            $rate = $this->nurseInfo->visit_fee_2;
                        }
                        break;
                    case 2:
                        if ($totalTime >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS
                            && (($this->practiceHasCcmPlus && ChargeableService::CCM === $csCode) || ChargeableService::RPM === $csCode)) {
                            $rate = $this->nurseInfo->visit_fee_3;
                        }
                        break;
                }
                break;
            case ChargeableService::PCM:
                switch ($rangeKey) {
                    case 0:
                        if ($totalTime >= self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM) {
                            $rate = $this->nurseInfo->visit_fee;
                        }
                        break;
                }
                break;
        }

        if (0.0 === $rate) {
            return null;
        }

        $nurseCcmPercentageInRange = $this->getNurseTimePercentageAllocationInRange(
            $this->nurseInfo->id,
            $csCode,
            $timeEntryForCsCodeForRangePerNurseInfoId
        );
        if (0 === $nurseCcmPercentageInRange) {
            return null;
        }

        $logDate = $timeEntryForCsCodeForRangePerNurseInfoId->has($this->nurseInfo->id)
            ? $timeEntryForCsCodeForRangePerNurseInfoId->get($this->nurseInfo->id)->lastLogDate : null;

        return new VisitFeePay($logDate, $nurseCcmPercentageInRange * $rate, $nurseCcmPercentageInRange);
    }

    private function patientHasAtLeastOneSuccessfulCall(Collection $timeEntryPerCsCodePerRangePerNurseInfoId)
    {
        return $timeEntryPerCsCodePerRangePerNurseInfoId
            ->filter(function (Collection $timeEntryForCsCodePerRangePerNurseInfoId) {
                return $timeEntryForCsCodePerRangePerNurseInfoId
                    ->filter(function (Collection $timeEntryForCsCodeForRangePerNurseInfoId) {
                        return $timeEntryForCsCodeForRangePerNurseInfoId
                            ->filter(function (TimeRangeEntry $entry) {
                                return $entry->hasSuccessfulCall;
                            })
                            ->isNotEmpty();
                    })
                    ->isNotEmpty();
            })
            ->isNotEmpty();
    }
}
