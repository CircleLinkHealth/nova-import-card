<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use App\Services\ActivityService;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientMonthlyServiceTime;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Config\NurseCcmPlusConfig;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VariablePayCalculator
{
    const HOUR_IN_SECONDS                        = 3600;
    const MONTHLY_TIME_TARGET_2X_IN_SECONDS      = 2400;
    const MONTHLY_TIME_TARGET_3X_IN_SECONDS      = 3600;
    const MONTHLY_TIME_TARGET_IN_SECONDS         = 1200;
    const MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM = 1800;

    protected Carbon $endDate;

    protected array $nurseInfoIds;

    protected Carbon $startDate;

    /**
     * Cache variable, holds care rate logs.
     */
    private ?Collection $nurseCareRateLogs = null;

    public function __construct(array $nurseInfoIds, Carbon $startDate, Carbon $endDate)
    {
        $this->nurseInfoIds = $nurseInfoIds;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
    }

    /**
     * @throws \Exception        when patient not found
     * @return CalculationResult
     */
    public function calculate(User $nurse)
    {
        $nurseInfo    = $nurse->nurseInfo;
        $careRateLogs = $this->getForNurses();

        $perPatient = $careRateLogs->mapToGroups(function ($e) {
            return [$e['patient_user_id'] => $e];
        });

        $totalPay                               = 0.0;
        $visitsPerPatientPerChargeableServiceId = collect();
        $ccmPlusAlgoEnabled                     = $this->isNewNursePayAlgoEnabled();
        $visitFeeBased                          = $ccmPlusAlgoEnabled && $this->isNewNursePayAltAlgoEnabledForUser($nurse->id);

        $perPatient->each(function (Collection $patientCareRateLogs) use (
            &$totalPay,
            $visitsPerPatientPerChargeableServiceId,
            $nurseInfo,
            $ccmPlusAlgoEnabled,
            $visitFeeBased
        ) {
            $patientUserId = $patientCareRateLogs->first()->patient_user_id;
            if ( ! $patientUserId) {
                //we reach here when we have old records
                $patientPayCalculation = $this->getPayForPatientWithDefaultAlgo(
                    $nurseInfo,
                    $patientCareRateLogs
                );
            } else {
                $patientPayCalculation = $this->getPayForPatient(
                    $patientUserId,
                    $patientCareRateLogs,
                    $nurseInfo,
                    $ccmPlusAlgoEnabled,
                    $visitFeeBased
                );
            }

            $totalPay += $patientPayCalculation->pay;

            if (optional($patientPayCalculation->visitsPerChargeableServiceCode)->isNotEmpty()) {
                $visitsPerPatientPerChargeableServiceId->put($patientUserId, $patientPayCalculation->visitsPerChargeableServiceCode);
            }
        });

        return new CalculationResult(
            $ccmPlusAlgoEnabled,
            $visitFeeBased,
            $visitsPerPatientPerChargeableServiceId,
            $totalPay
        );
    }

    public function getForNurses()
    {
        if ($this->nurseCareRateLogs) {
            return $this->nurseCareRateLogs;
        }

        $nurseCareRateLogTable   = (new NurseCareRateLog())->getTable();
        $nurseInfoTable          = (new Nurse())->getTable();
        $this->nurseCareRateLogs = NurseCareRateLog
            ::select(["$nurseCareRateLogTable.*", "$nurseInfoTable.start_date"])
                ->leftJoin($nurseInfoTable, "$nurseInfoTable.id", '=', "$nurseCareRateLogTable.nurse_id")
                ->whereIn("$nurseCareRateLogTable.patient_user_id", function ($query) use ($nurseCareRateLogTable) {
                    $query->select('patient_user_id')
                        ->from($nurseCareRateLogTable)
                        ->whereIn('nurse_id', $this->nurseInfoIds)
                        ->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->groupBy('patient_user_id');
                })
                ->whereBetween("$nurseCareRateLogTable.created_at", [$this->startDate, $this->endDate])
                ->where(function ($q) use ($nurseCareRateLogTable, $nurseInfoTable) {
                    $q->whereNull("$nurseInfoTable.start_date")
                        ->orWhere(DB::raw("DATE($nurseCareRateLogTable.performed_at)"), '>=', DB::raw("DATE($nurseInfoTable.start_date)"));
                })
                ->get();

        return $this->nurseCareRateLogs;
    }

    private function arrangeSlotsForRange(
        Collection $range,
        int $nurseInfoId,
        bool $isSuccessfulCall,
        string $logDate,
        TimeSlots $slots
    ) {
        if ($slots->towards20) {
            /** @var Collection $rangeTowards20 */
            $rangeTowards20 = $range->get(0, collect());
            $rangeTowards20->put($nurseInfoId, $this->getEntryForRange(
                $rangeTowards20,
                $nurseInfoId,
                $slots->towards20,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(0, $rangeTowards20);
        }
        if ($slots->after20) {
            /** @var Collection $rangeAfter20 */
            $rangeAfter20 = $range->get(1, collect());
            $rangeAfter20->put($nurseInfoId, $this->getEntryForRange(
                $rangeAfter20,
                $nurseInfoId,
                $slots->after20,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(1, $rangeAfter20);
        }
        if ($slots->after40) {
            /** @var Collection $rangeAfter40 */
            $rangeAfter40 = $range->get(2, collect());
            $rangeAfter40->put($nurseInfoId, $this->getEntryForRange(
                $rangeAfter40,
                $nurseInfoId,
                $slots->after40,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(2, $rangeAfter40);
        }
        if ($slots->after60) {
            /** @var Collection $rangeAfter60 */
            $rangeAfter60 = $range->get(3, collect());
            $rangeAfter60->put($nurseInfoId, $this->getEntryForRange(
                $rangeAfter60,
                $nurseInfoId,
                $slots->after60,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(3, $rangeAfter60);
        }

        if ($slots->towards30) {
            /** @var Collection $rangeTowards30 */
            $rangeTowards30 = $range->get(0, collect());
            $rangeTowards30->put($nurseInfoId, $this->getEntryForRange(
                $rangeTowards30,
                $nurseInfoId,
                $slots->towards30,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(0, $rangeTowards30);
        }
        if ($slots->after30) {
            /** @var Collection $rangeAfter30 */
            $rangeAfter30 = $range->get(1, collect());
            $rangeAfter30->put($nurseInfoId, $this->getEntryForRange(
                $rangeAfter30,
                $nurseInfoId,
                $slots->after30,
                $isSuccessfulCall,
                $logDate
            ));
            $range->put(1, $rangeAfter30);
        }

        return $range;
    }

    private function getBillableEventIfOnlyOne(Collection $timeEntryPerCsCodePerRangePerNurseInfoId, int $patientId, bool $practiceHasCcmPlus)
    {
        $noOfBillableEvents = 0;
        /** @var ?string $lastBillableCode */
        $lastBillableCode = null;
        $timeEntryPerCsCodePerRangePerNurseInfoId
            ->keys()
            ->each(function ($csCode) use (&$noOfBillableEvents, &$lastBillableCode, $patientId, $practiceHasCcmPlus) {
                $totalTimeForMonth = $this->getTotalTimeForMonth($csCode, $patientId);
                switch ($csCode) {
                    case ChargeableService::CCM:
                    case ChargeableService::RPM:
                    case ChargeableService::BHI:
                        if ($totalTimeForMonth >= self::MONTHLY_TIME_TARGET_IN_SECONDS) {
                            ++$noOfBillableEvents;
                            $lastBillableCode = $csCode;
                        }
                        if ($totalTimeForMonth >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
                            if (($practiceHasCcmPlus && ChargeableService::CCM === $csCode) || ChargeableService::RPM) {
                                ++$noOfBillableEvents;
                                $lastBillableCode = $csCode;
                            }
                        }
                        if ($totalTimeForMonth >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS && $practiceHasCcmPlus && ChargeableService::CCM === $csCode) {
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

    private function getEntryForRange(
        Collection $range,
        int $nurseInfoId,
        int $newDuration,
        bool $successfulCall,
        string $logDate
    ): TimeRangeEntry {
        /** @var ?TimeRangeEntry $prev */
        $prev = null;
        if ($range->has($nurseInfoId)) {
            $prev = $range->get($nurseInfoId);
        }

        $duration          = $newDuration;
        $hasSuccessfulCall = $successfulCall;
        if ($prev) {
            if ( ! $hasSuccessfulCall && $prev->hasSuccessfulCall) {
                $hasSuccessfulCall = true;
            }

            $duration += $prev->duration;
        }

        return new TimeRangeEntry($duration, $hasSuccessfulCall, $logDate);
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

    /**
     * @throws \Exception
     * @return PatientPayCalculationResult
     */
    private function getPayForPatient(
        int $patientUserId,
        Collection $patientCareRateLogs,
        Nurse $nurseInfo,
        bool $ccmPlusAlgoEnabled,
        bool $visitFeeBased
    ) {
        /** @var User $patient */
        $patient = User::withTrashed()
            ->with([
                'primaryPractice.chargeableServices',
                'patientSummaries' => function ($q) {
                    $q->whereMonthYear(($this->startDate ?? now())->startOfMonth())
                        ->orderBy('id', 'desc');
                },
            ])
            ->find($patientUserId);
        if ( ! $patient) {
            throw new \Exception("Could not find user with id $patientUserId");
        }

        if ($patient->primaryPractice->is_demo) {
            return PatientPayCalculationResult::withVisits(collect());
        }

        if ( ! $ccmPlusAlgoEnabled) {
            return $this->getPayForPatientWithDefaultAlgo(
                $nurseInfo,
                $patientCareRateLogs
            );
        }

        $practiceHasCcmPlus = $this->practiceHasCcmPlusCode($patient->primaryPractice);

        //PCM payment is always Visit Fee based
        $patientIsPcm = $patient->isPcm();
        if ( ! $visitFeeBased && $patientIsPcm) {
            $visitFeeBased = true;
        }

        $timeEntryPerCsCodePerRangePerNurseInfoId = $this->separateTimeAccruedInRanges($patientCareRateLogs);

        if ($visitFeeBased) {
            return $this->getPayForPatientWithVisitFeeAlgo(
                $nurseInfo,
                $patient->id,
                $timeEntryPerCsCodePerRangePerNurseInfoId,
                $practiceHasCcmPlus
            );
        }

        return $this->getPayForPatientWithVariableRateAlgo(
            $nurseInfo,
            $patient->id,
            $timeEntryPerCsCodePerRangePerNurseInfoId,
            $practiceHasCcmPlus
        );
    }

    /**
     * @return PatientPayCalculationResult
     */
    private function getPayForPatientWithDefaultAlgo(
        Nurse $nurseInfo,
        Collection $patientCareRateLogs
    ) {
        $towardsCCm = $patientCareRateLogs
            ->where('nurse_id', '=', $nurseInfo->id)
            ->where('ccm_type', '=', 'accrued_towards_ccm')
            ->sum('increment');
        $towardsCCm = $towardsCCm / self::HOUR_IN_SECONDS;

        $afterCCm = $patientCareRateLogs
            ->where('nurse_id', '=', $nurseInfo->id)
            ->where('ccm_type', '=', 'accrued_after_ccm')
            ->sum('increment');
        $afterCCm = $afterCCm / self::HOUR_IN_SECONDS;

        $highRates = collect([$towardsCCm * $nurseInfo->high_rate]);
        $lowRates  = collect([$afterCCm * $nurseInfo->low_rate]);

        return PatientPayCalculationResult::withHighLowRates($highRates, $lowRates);
    }

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
     *
     * @return PatientPayCalculationResult
     */
    private function getPayForPatientWithVariableRateAlgo(
        Nurse $nurseInfo,
        int $patientId,
        Collection $timeEntryPerCsCodePerRangePerNurseInfoId,
        bool $practiceHasCcmPlus
    ) {
        $highRates = collect();
        $lowRates  = collect();

        $nurseInfoId = $nurseInfo->id;

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

        $hasSuccessfulCall = false;
        $rangesForNurseOnly->each(function (Collection $timeEntryForCsCodePerRange) use (&$hasSuccessfulCall) {
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

        $rangesForNurseOnly->each(function (Collection $timeEntryForCsCodePerRange, string $csCode) use ($nurseInfo, $patientId, $hasSuccessfulCall, $lowRates, $highRates, $practiceHasCcmPlus) {
            if ($timeEntryForCsCodePerRange->isEmpty()) {
                return;
            }

            //pcm not supported for this algo
            if (ChargeableService::PCM === $csCode) {
                return;
            }

            $totalTime = $this->getTotalTimeForMonth($csCode, $patientId);
            $timeEntryForCsCodePerRange->each(function (TimeRangeEntry $entry, int $rangeKey) use ($csCode, $nurseInfo, $patientId, $hasSuccessfulCall, $lowRates, $highRates, $totalTime, $practiceHasCcmPlus) {
                $pay = $this->getVariableRatePayForRange(
                    $nurseInfo,
                    $hasSuccessfulCall,
                    $practiceHasCcmPlus,
                    $totalTime,
                    $csCode,
                    $rangeKey,
                    $entry
                );
                if ( ! $pay) {
                    return;
                }
                if ($pay->rate === $nurseInfo->low_rate) {
                    $lowRates->push($pay->pay);
                } else {
                    $highRates->push($pay->pay);
                }
            });
        });

        return PatientPayCalculationResult::withHighLowRates($highRates, $lowRates);
    }

    /**
     * @param $patientId
     * @param $totalCcm
     * @param $totalBhi
     * @param $rangesPerType
     *
     * @return PatientPayCalculationResult
     */
    private function getPayForPatientWithVisitFeeAlgo(
        Nurse $nurseInfo,
        int $patientId,
        Collection $timeEntryPerCsCodePerRangePerNurseInfoId,
        bool $practiceHasCcmPlus
    ) {
        $visitsPerChargeableServiceCodePerDay = collect();

        $patientHasAtLeastOneSuccessfulCall = $this->patientHasAtLeastOneSuccessfulCall($timeEntryPerCsCodePerRangePerNurseInfoId);
        if ( ! $patientHasAtLeastOneSuccessfulCall) {
            return PatientPayCalculationResult::withVisits($visitsPerChargeableServiceCodePerDay);
        }

        // CPM-1997
        // If only 1 billable event, the RN(s) with successful call(s) split VF proportionally,
        // any other RNs spending time without a successful call get 0%.
        $singleBillableEvent = $this->getBillableEventIfOnlyOne($timeEntryPerCsCodePerRangePerNurseInfoId, $patientId, $practiceHasCcmPlus);

        if ($singleBillableEvent) {
            $pay = $this->getVisitFeePayForOneBillableEvent(
                $nurseInfo,
                $patientId,
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
        } else {
            $timeEntryPerCsCodePerRangePerNurseInfoId->each(function (Collection $timeEntryForCsCodePerRangePerNurseInfoId, string $csCode) use ($patientId, $nurseInfo, $visitsPerChargeableServiceCodePerDay, $practiceHasCcmPlus) {
                $totalTime = $this->getTotalTimeForMonth($csCode, $patientId);
                $timeEntryForCsCodePerRangePerNurseInfoId->each(function (Collection $timeEntryForCsCodeForRangePerNurseInfoId, int $rangeKey) use ($csCode, $totalTime, $nurseInfo, $visitsPerChargeableServiceCodePerDay, $practiceHasCcmPlus) {
                    if ($timeEntryForCsCodeForRangePerNurseInfoId->isEmpty()) {
                        return;
                    }

                    $pay = $this->getVisitFeePayForRange(
                        $nurseInfo,
                        $csCode,
                        $totalTime,
                        $rangeKey,
                        $timeEntryForCsCodeForRangePerNurseInfoId,
                        $practiceHasCcmPlus
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

        return PatientPayCalculationResult::withVisits($visitsPerChargeableServiceCodePerDay);
    }

    private function getTimeSlotsForChargeableService(
        int $totalTimeBefore,
        int $duration,
        string $csCode
    ): TimeSlots {
        $splitter                  = new TimeSplitter();
        $splitFor30MinuteIntervals = ChargeableService::PCM === $csCode;
        $splitUpTo40Plus           = ChargeableService::RPM || ChargeableService::RPM40;
        $splitUpTo60Plus           = ChargeableService::CCM || ChargeableService::CCM_PLUS_40 || ChargeableService::CCM_PLUS_60;

        return $splitter->split($totalTimeBefore, $duration, $splitFor30MinuteIntervals, $splitUpTo40Plus, $splitUpTo60Plus);
    }

    private function getTotalTimeForMonth(string $csCode, int $patientId): int
    {
        $month = $this->startDate->copy()->startOfMonth();
        if (Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG)) {
            return PatientMonthlyServiceTime::forChargeableServiceCode($csCode, $patientId, $month);
        }

        $csId = ChargeableService::firstWhere('code', '=', $csCode)->id;

        return app(ActivityService::class)->totalTimeForChargeableServiceId($patientId, $csId, $month);
    }

    private function getVariableRatePayForRange(
        Nurse $nurseInfo,
        bool $hasSuccessfulCall,
        bool $practiceHasCcmPlus,
        int $totalTime,
        string $csCode,
        int $rangeKey,
        TimeRangeEntry $range
    ): ?VariableRatePay {
        if (empty($range->duration)) {
            return null;
        }

        $rate = $nurseInfo->low_rate;

        switch ($rangeKey) {
            case 0:
                //0-20 always pays high rate
                $rate = $nurseInfo->high_rate;
                break;
            case 1:
                if (in_array($csCode, [ChargeableService::CCM, ChargeableService::RPM]) && $hasSuccessfulCall && $practiceHasCcmPlus &&
                    $totalTime >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
                    $rate = $nurseInfo->high_rate_2;
                }
                break;
            case 2:
                if (ChargeableService::CCM === $csCode && $hasSuccessfulCall && $practiceHasCcmPlus &&
                    $totalTime >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS) {
                    $rate = $nurseInfo->high_rate_3;
                }
                break;
            default:
                break;
        }

        $nurseCcmInRange = $range->duration / self::HOUR_IN_SECONDS;

        return new VariableRatePay($nurseCcmInRange * $rate, $rate);
    }

    private function getVisitFeePayForOneBillableEvent(
        Nurse $nurseInfo,
        int $patientId,
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
            $nurseUserId = $nurseInfo->user->id;
            sendSlackMessage(
                '#nurse-invoices-alerts',
                "Warning: Will not pay care coach [$nurseUserId] for time tracked on patient [$patientId] because I could not find successful call in single billable event [$chargeableServiceCode]"
            );

            return new VisitFeePay(null, 0, 0);
        }

        /** @var TimeRangeEntry $nurseEntry */
        $nurseEntry = $nursesTimes->get(
            $nurseInfo->id,
            new TimeRangeEntry(0, false, null)
        );

        $count = ($nurseEntry->duration / $sumOfAllTime);

        return new VisitFeePay(
            $nurseEntry->lastLogDate ?? null,
            $count * $nurseInfo->visit_fee,
            $count
        );
    }

    private function getVisitFeePayForRange(
        Nurse $nurseInfo,
        string $csCode,
        int $totalTime,
        int $rangeKey,
        Collection $timeEntryForCsCodeForRangePerNurseInfoId,
        bool $practiceHasCcmPlus
    ): ?VisitFeePay {
        $rate = 0.0;

        switch ($csCode) {
            case ChargeableService::CCM:
            case ChargeableService::BHI:
            case ChargeableService::RPM:
            switch ($rangeKey) {
                    case 0:
                        if ($totalTime >= self::MONTHLY_TIME_TARGET_IN_SECONDS) {
                            $rate = $nurseInfo->visit_fee;
                        }
                        break;
                    case 1:
                        if ($totalTime >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
                            if (($practiceHasCcmPlus && ChargeableService::CCM === $csCode) || ChargeableService::RPM === $csCode) {
                                $rate = $nurseInfo->visit_fee_2;
                            }
                        }
                        break;
                    case 2:
                        if (ChargeableService::CCM === $csCode && $practiceHasCcmPlus && $totalTime >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS) {
                            $rate = $nurseInfo->visit_fee_3;
                        }
                        break;
                }
                break;
            case ChargeableService::PCM:
                switch ($rangeKey) {
                    case 0:
                        if ($totalTime >= self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM) {
                            $rate = $nurseInfo->visit_fee;
                        }
                        break;
                }
                break;
        }

        if (0.0 === $rate) {
            return null;
        }

        $nurseCcmPercentageInRange = $this->getNurseTimePercentageAllocationInRange(
            $nurseInfo->id,
            $csCode,
            $timeEntryForCsCodeForRangePerNurseInfoId
        );
        if (0 === $nurseCcmPercentageInRange) {
            return null;
        }

        $logDate = $timeEntryForCsCodeForRangePerNurseInfoId->has($nurseInfo->id)
            ? $timeEntryForCsCodeForRangePerNurseInfoId->get($nurseInfo->id)->lastLogDate : null;

        return new VisitFeePay($logDate, $nurseCcmPercentageInRange * $rate, $nurseCcmPercentageInRange);
    }

    private function isNewNursePayAlgoEnabled()
    {
        return NurseCcmPlusConfig::enabledForAll();
    }

    private function isNewNursePayAltAlgoEnabledForUser(
        $nurseUserId
    ) {
        if (NurseCcmPlusConfig::altAlgoEnabledForAll()) {
            return true;
        }

        $enabledForUserIds = NurseCcmPlusConfig::altAlgoEnabledForUserIds();
        if ($enabledForUserIds) {
            return in_array($nurseUserId, $enabledForUserIds);
        }

        return false;
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

    private function practiceHasCcmPlusCode(
        Practice $practice
    ) {
        return Cache::store('array')->rememberForever("ccm_plus_$practice->id", function () use ($practice) {
            return $practice->hasCCMPlusServiceCode();
        });
    }

    private function separateTimeAccruedInRanges(
        Collection $patientCareRateLogs
    ) {
        /**
         * CCM (+ CCM40, CCM60)
         * 0 => 0-20
         * 1 => 20-40
         * 2 => 40-60
         * 3 => 60+.
         *
         * BHI
         * 0 => 0-20
         * 1 => 20+.
         *
         * PCM
         * 0 => 0-30
         * 1 => 30+.
         *
         * RPM (+ RPM40)
         * 0 => 0-20
         * 1 => 20-40
         * 2 => 40+
         */
        $timeEntryPerCsCodePerRangePerNurseInfoId = collect();
        $chargeableServices                       = ChargeableService::getAll();

        $patientCareRateLogs->each(function ($e) use ($timeEntryPerCsCodePerRangePerNurseInfoId, $chargeableServices) {
            $chargeableServiceId = $e['chargeable_service_id'];
            if ( ! $chargeableServiceId) {
                return;
            }

            $nurseInfoId = $e['nurse_id'];
            $isSuccessfulCall = $e['is_successful_call'];
            $duration = $e['increment'];
            $totalTimeBefore = $e['time_before'];

            // performed_at is a new field, we revert to created_at if it is null
            $logDate = ($e['performed_at'] ?? $e['created_at'])->toDateString();

            $csCode = $chargeableServices->where('id', '=', $chargeableServiceId)->first()->code;
            $slots = $this->getTimeSlotsForChargeableService($totalTimeBefore, $duration, $csCode);

            if (in_array($csCode, ChargeableService::CCM_PLUS_CODES)) {
                $csCode = ChargeableService::CCM;
            } elseif (in_array($csCode, ChargeableService::RPM_CODES)) {
                $csCode = ChargeableService::RPM;
            }

            /** @var Collection $range */
            $range = $timeEntryPerCsCodePerRangePerNurseInfoId->get($csCode, collect());
            $range = $this->arrangeSlotsForRange($range, $nurseInfoId, $isSuccessfulCall, $logDate, $slots);

            $timeEntryPerCsCodePerRangePerNurseInfoId->put($csCode, $range);
        });

        $timeEntryPerCsCodePerRangePerNurseInfoId->each(function (Collection $rangePerChargeableServiceCode) {
            $this->setSuccessfulCallBasedOnPreviousRange($rangePerChargeableServiceCode);
        });

        return $timeEntryPerCsCodePerRangePerNurseInfoId;
    }

    /**
     * If a range is in the same day as an other range with a successful call,
     * make sure that both have successful call as true
     * This covers the case where a care coach makes a call that spans over two ranges.
     */
    private function setSuccessfulCallBasedOnPreviousRange(Collection $coll)
    {
        $nurseCallsInDays = collect();
        $coll->each(function (Collection $nurseRange) use ($nurseCallsInDays) {
            $nurseRange->each(function (TimeRangeEntry $range, string $nurseInfoId) use ($nurseCallsInDays) {
                if ( ! $range->hasSuccessfulCall) {
                    return;
                }
                $entry = $nurseCallsInDays->get($nurseInfoId, []);
                if ( ! in_array($range->lastLogDate, $entry)) {
                    $entry[] = $range->lastLogDate;
                }
                $nurseCallsInDays->put($nurseInfoId, $entry);
            });
        });
        $coll->each(function (Collection $nurseRange, $rangeIndex) use ($coll, $nurseCallsInDays) {
            $nurseRange->each(function (TimeRangeEntry $range, string $nurseInfoId) use ($coll, $rangeIndex, $nurseCallsInDays) {
                $entry = $nurseCallsInDays->get($nurseInfoId, []);
                if (in_array($range->lastLogDate, $entry)) {
                    $range->hasSuccessfulCall = true;
                }
            });
        });
    }
}
