<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Config\NurseCcmPlusConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VariablePayCalculator
{
    const HOUR_IN_SECONDS = 3600;
    const MONTHLY_TIME_TARGET_2X_IN_SECONDS = 2400;
    const MONTHLY_TIME_TARGET_3X_IN_SECONDS = 3600;
    const MONTHLY_TIME_TARGET_IN_SECONDS = 1200;
    const MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM = 1800;

    /**
     * @var Carbon
     */
    protected $endDate;

    /**
     * @var array
     */
    protected $nurseInfoIds;

    /**
     * @var Carbon
     */
    protected $startDate;

    /**
     * Cache variable, holds care rate logs.
     *
     * @var Collection|null
     */
    private $nurseCareRateLogs;

    public function __construct(array $nurseInfoIds, Carbon $startDate, Carbon $endDate)
    {
        $this->nurseInfoIds = $nurseInfoIds;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
    }

    /**
     * @param User $nurse
     *
     * @return CalculationResult
     *
     * @throws \Exception when patient not found
     */
    public function calculate(User $nurse)
    {
        $nurseUserId  = $nurse->id;
        $nurseInfo    = $nurse->nurseInfo;
        $careRateLogs = $this->getForNurses();

        $perPatient = $careRateLogs->mapToGroups(function ($e) {
            return [$e['patient_user_id'] => $e];
        });

        $totalPay           = 0.0;
        $visits             = [];
        $bhiVisits          = [];
        $pcmVisits          = [];
        $ccmPlusAlgoEnabled = $this->isNewNursePayAlgoEnabled();
        $visitFeeBased      = $ccmPlusAlgoEnabled && $this->isNewNursePayAltAlgoEnabledForUser($nurseUserId);

        $perPatient->each(function (Collection $patientCareRateLogs) use (
            &$totalPay,
            &$visits,
            &$bhiVisits,
            &$pcmVisits,
            $nurseUserId,
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
            if ( ! empty($patientPayCalculation->visits)) {
                $visits[$patientUserId] = $patientPayCalculation->visits;
            }

            if ( ! empty($patientPayCalculation->bhiVisits)) {
                $bhiVisits[$patientUserId] = $patientPayCalculation->bhiVisits;
            }

            if ( ! empty($patientPayCalculation->pcmVisits)) {
                $pcmVisits[$patientUserId] = $patientPayCalculation->pcmVisits;
            }
        });

        return new CalculationResult($ccmPlusAlgoEnabled, $visitFeeBased, $visits, $bhiVisits, $pcmVisits, $totalPay);
    }

    public function getForNurses()
    {
        if ($this->nurseCareRateLogs) {
            return $this->nurseCareRateLogs;
        }

        $this->nurseCareRateLogs = NurseCareRateLog::whereIn('patient_user_id', function ($query) {
            $query->select('patient_user_id')
                  ->from((new NurseCareRateLog())->getTable())
                  ->whereIn('nurse_id', $this->nurseInfoIds)
                  ->whereBetween('created_at', [$this->startDate, $this->endDate])
                  ->distinct();
        })
                                                   ->whereBetween('created_at', [$this->startDate, $this->endDate])
                                                   ->get();

        return $this->nurseCareRateLogs;
    }

    private function getEntryForRange($ranges, $index, $nurseInfoId, $newDuration, $successfulCall)
    {
        $range = $ranges[$index];
        $prev  = null;
        if (array_key_exists($nurseInfoId, $range)) {
            $prev = $ranges[$index][$nurseInfoId];
        }

        $duration          = $newDuration;
        $hasSuccessfulCall = $successfulCall;
        if ($prev) {
            if ( ! $hasSuccessfulCall && $prev['has_successful_call']) {
                $hasSuccessfulCall = true;
            }

            $duration += $prev['duration'];
        }

        return [
            'duration'            => $duration,
            'has_successful_call' => $hasSuccessfulCall,
        ];
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
     * @param bool $isPcm
     *
     * @return float
     */
    private function getNurseTimePercentageAllocationInRange(
        $nurseInfoId,
        $range,
        bool $isPcm
    ): float {
        $elqRange = collect($range);

        //only 1 RN, pay the full VF, regardless of calls
        if (1 === $elqRange->count()) {
            return $elqRange->has($nurseInfoId)
                ? 1
                : 0;
        }

        $filtered = $elqRange->filter(function ($f) {
            return $f['has_successful_call'];
        });

        //none of them had successful calls
        //or all RNs had successful calls, split the VF proportionally
        if ($filtered->isEmpty() || $elqRange->count() === $filtered->count()) {
            $target = $isPcm
                ? self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM
                : self::MONTHLY_TIME_TARGET_IN_SECONDS;

            return $elqRange[$nurseInfoId]['duration'] / $target;
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
        $sumOfAllWithCall = $filtered->sum(function ($f) {
            return $f['duration'];
        });

        return $filtered[$nurseInfoId]['duration'] / $sumOfAllWithCall;
    }

    /**
     * @param $patientUserId
     * @param $patientCareRateLogs
     * @param Nurse $nurseInfo
     * @param bool $ccmPlusAlgoEnabled
     * @param bool $visitFeeBased
     *
     * @return PatientPayCalculationResult
     * @throws \Exception
     */
    private function getPayForPatient(
        $patientUserId,
        $patientCareRateLogs,
        Nurse $nurseInfo,
        bool $ccmPlusAlgoEnabled,
        bool $visitFeeBased
    ) {
        if ( ! $ccmPlusAlgoEnabled) {
            return $this->getPayForPatientWithDefaultAlgo(
                $nurseInfo,
                $patientCareRateLogs
            );
        }

        $patient = User::with('primaryPractice.chargeableServices')->find($patientUserId);
        if ( ! $patient) {
            throw new \Exception("Could not find user with id $patientUserId");
        }

        /** @var PatientMonthlySummary $patientSummary */
        $patientSummary = $patient->patientSummaryForMonth($this->startDate);
        if ( ! $patientSummary) {
            $month = $this->startDate->toDateString();
            throw new \Exception("Could not find patient summary for user $patientUserId and month $month");
        }

        $practiceHasCcmPlus = $this->practiceHasCcmPlusCode($patient->primaryPractice);

        //PCM payment is always Visit Fee based
        $patientIsPcm = $patient->isPcm();
        if ( ! $visitFeeBased && $patientIsPcm) {
            $visitFeeBased = true;
        }

        $totalCcm = $patientSummary->ccm_time;
        $totalBhi = $patientSummary->bhi_time;
        $ranges   = $this->separateTimeAccruedInRanges($patientCareRateLogs);

        if ($visitFeeBased) {
            return $this->getPayForPatientWithCcmPlusAltAlgo(
                $nurseInfo,
                $patientUserId,
                $totalCcm,
                $totalBhi,
                $ranges,
                $practiceHasCcmPlus,
                $patientIsPcm
            );
        } else {
            return $this->getPayForPatientWithCcmPlusAlgo(
                $nurseInfo,
                $totalCcm,
                $totalBhi,
                $ranges,
                $practiceHasCcmPlus
            );
        }

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
     * @param Nurse $nurseInfo
     * @param $totalCcm
     * @param $totalBhi
     * @param $ranges
     * @param bool $practiceHasCcmPlus
     *
     * @return PatientPayCalculationResult
     */
    private function getPayForPatientWithCcmPlusAlgo(
        Nurse $nurseInfo,
        $totalCcm,
        $totalBhi,
        $ranges,
        $practiceHasCcmPlus = false
    ) {
        $highRates = [];
        $lowRates  = [];

        $nurseInfoId = $nurseInfo->id;

        $rangesForNurseOnly = collect($ranges)
            ->map(function ($r) use ($nurseInfoId) {
                return collect($r)
                    ->map(function ($rangeType) use ($nurseInfoId) {
                        return array_key_exists($nurseInfoId, $rangeType)
                            ? $rangeType[$nurseInfoId]
                            : [];
                    })
                    ->filter();
            })
            ->filter();

        /** @var Collection $ccmRanges */
        $ccmRanges = $rangesForNurseOnly['ccm'];
        /** @var Collection $pcmRanges */
        $pcmRanges = $rangesForNurseOnly['pcm'];
        /** @var Collection $bhiRanges */
        $bhiRanges = $rangesForNurseOnly['bhi'];

        $hasSuccessfulCall = $ccmRanges
            ->filter(function ($f) {
                return $f['has_successful_call'];
            })
            ->isNotEmpty();
        if ( ! $hasSuccessfulCall) {
            $hasSuccessfulCall = $bhiRanges
                ->filter(function ($f) {
                    return $f['has_successful_call'];
                })->isNotEmpty();
        }
        if ( ! $hasSuccessfulCall) {
            $hasSuccessfulCall = $pcmRanges
                ->filter(function ($f) {
                    return $f['has_successful_call'];
                })
                ->isNotEmpty();
        }

        foreach ($rangesForNurseOnly as $rangeType) {
            foreach ($rangeType as $key => $value) {
                if (0 === sizeof($value)) {
                    continue;
                }

                if ($key === 'pcm') {
                    //pcm not supported for this algo
                    continue;
                }

                $isBehavioral = $key === 'bhi';
                $time         = $isBehavioral
                    ? $totalBhi
                    : $totalCcm;

                $pay = $this->getVariableRatePayForRange($nurseInfo, $key, $value, $hasSuccessfulCall,
                    $practiceHasCcmPlus, $time, $isBehavioral);
                if ($pay) {
                    if ($pay['rate'] === $nurseInfo->low_rate) {
                        $lowRates[] = $pay['pay'];
                    } else {
                        $highRates[] = $pay['pay'];
                    }
                }
            }
        }

        return PatientPayCalculationResult::withHighLowRates($highRates, $lowRates);
    }

    private function getVariableRatePayForRange(
        Nurse $nurseInfo,
        $rangeKey,
        $range,
        bool $hasSuccessfulCall,
        bool $practiceHasCcmPlus,
        $totalTime,
        bool $isBehavioral
    ) {
        if (empty($range['duration'])) {
            return null;
        }

        $rate = $nurseInfo->low_rate;

        switch ($rangeKey) {
            case 0:
                //0-20 always pays high rate
                $rate = $nurseInfo->high_rate;
                break;
            case 1:
                if ( ! $isBehavioral && $hasSuccessfulCall && $practiceHasCcmPlus &&
                     $totalTime >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
                    $rate = $nurseInfo->high_rate_2;
                }
                break;
            case 2:
                if ( ! $isBehavioral && $hasSuccessfulCall && $practiceHasCcmPlus &&
                     $totalTime >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS) {
                    $rate = $nurseInfo->high_rate_3;
                }
                break;
            default:
                break;
        }

        $nurseCcmInRange = $range['duration'] / self::HOUR_IN_SECONDS;

        return [
            'pay'  => $nurseCcmInRange * $rate,
            'rate' => $rate,
        ];
    }

    /**
     * @param Nurse $nurseInfo
     * @param $patientId
     * @param $totalCcm
     * @param $totalBhi
     * @param $rangesPerType
     * @param bool $practiceHasCcmPlus
     * @param bool $isPcmBillable
     *
     * @return PatientPayCalculationResult
     */
    private function getPayForPatientWithCcmPlusAltAlgo(
        Nurse $nurseInfo,
        $patientId,
        $totalCcm,
        $totalBhi,
        $rangesPerType,
        bool $practiceHasCcmPlus = false,
        bool $isPcmBillable = false
    ) {
        $visits    = [];
        $bhiVisits = [];
        $pcmVisits = [];

        $patientHasAtLeastOneSuccessfulCall = collect($rangesPerType)
            ->filter(function ($f) {
                //$f => ccm, bhi, pc,
                return collect($f)
                    ->filter(function ($f2) {
                        //$f2 => nurse info id
                        return collect($f2)
                            ->filter(function ($f3) {
                                //$f3 => ['has_successful_call', 'duration']
                                return $f3['has_successful_call'];
                            })
                            ->isNotEmpty();
                    })
                    ->isNotEmpty();
            })
            ->isNotEmpty();

        if ( ! $patientHasAtLeastOneSuccessfulCall) {
            return PatientPayCalculationResult::withVisits($visits, $bhiVisits, $pcmVisits);
        }

        // CPM-1997
        // If only 1 billable event, the RN(s) with successful call(s) split VF proportionally,
        // any other RNs spending time without a successful call get 0%.
        $noOfBillableEvents = 0;

        if ($isPcmBillable) {
            if ($totalCcm >= self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM) {
                $noOfBillableEvents++;
            }
        } else {
            if ($totalCcm >= self::MONTHLY_TIME_TARGET_IN_SECONDS) {
                $noOfBillableEvents++;
            }
            if ($practiceHasCcmPlus && $totalCcm >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
                $noOfBillableEvents++;
            }
            if ($practiceHasCcmPlus && $totalCcm >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS) {
                $noOfBillableEvents++;
            }
        }

        $isBhiBillable = $totalBhi >= self::MONTHLY_TIME_TARGET_IN_SECONDS;
        if ($isBhiBillable) {
            $noOfBillableEvents++;
        }

        if ($noOfBillableEvents === 1) {
            $pay = $this->getVisitFeePayForOneBillableEvent($nurseInfo, $patientId, $rangesPerType, $isBhiBillable,
                $isPcmBillable);
            if ( ! $isBhiBillable) {
                $visits[0] = $pay;
            } else {
                $bhiVisits[0] = $pay;
            }
        } else {
            //if total ccm is greater than the range, then we can pay that range
            foreach ($rangesPerType as $rangeType => $ranges) {

                $isBehavioral = $rangeType === 'bhi';
                $time         = $isBehavioral
                    ? $totalBhi
                    : $totalCcm;

                foreach ($ranges as $key => $range) {

                    if (empty($range)) {
                        continue;
                    }

                    $pay = $this->getVisitFeePayForRange($nurseInfo, $key, $range, $practiceHasCcmPlus, $time,
                        $isBehavioral, $isPcmBillable);

                    if ($pay) {
                        if ($isBehavioral) {
                            $bhiVisits[$key] = $pay;
                        } else if ($isPcmBillable) {
                            $pcmVisits[$key] = $pay;
                        } else {
                            $visits[$key] = $pay;
                        }
                    }
                }
            }
        }

        return PatientPayCalculationResult::withVisits($visits, $bhiVisits, $pcmVisits);
    }

    private
    function getVisitFeePayForOneBillableEvent(
        Nurse $nurseInfo,
        $patientId,
        $ranges,
        $isBehavioral,
        $isPcm
    ) {
        $elqRange = $isBehavioral
            ? $ranges['bhi']
            : ($isPcm
                ? $ranges['pcm']
                : $ranges['ccm']);
        $elqRange = collect($elqRange);

        // calculate time for each nurse that has a successful call
        $nurseTimes = collect();
        $elqRange->each(function ($f) use ($nurseTimes) {

            /** @var Collection $f */
            $f = collect($f);

            return $f->each(function ($f2, $key) use ($nurseTimes) {
                $current = $nurseTimes->get($key, ['duration' => 0, 'has_successful_call' => false]);
                $nurseTimes->put($key, [
                    'duration'            => $current['duration'] + $f2['duration'],
                    'has_successful_call' => $current['has_successful_call'] || $f2['has_successful_call'],
                ]);
            });
        });

        $sumOfAllTime = 0;
        $nurseTimes   = $nurseTimes->filter(function ($item) use (&$sumOfAllTime) {
            $val = $item['has_successful_call'];

            if ($val) {
                $sumOfAllTime += $item['duration'];
            }

            return $val;
        });

        if ($sumOfAllTime === 0) {
            $nurseUserId   = $nurseInfo->user->id;
            $billableEvent = $isBehavioral
                ? 'bhi'
                : 'ccm';
            sendSlackMessage(
                '#nurse-invoices-alerts',
                "Warning: Will not pay care coach [$nurseUserId] for time tracked on patient [$patientId] because I could not find successful call in billable event [$billableEvent]"
            );

            return 0;
        }

        $nurseTime = $nurseTimes->get($nurseInfo->id,
            ['duration' => 0, 'has_successful_call' => false])['duration'];

        return ($nurseTime / $sumOfAllTime) * $nurseInfo->visit_fee;
    }

    private
    function getVisitFeePayForRange(
        Nurse $nurseInfo,
        $rangeKey,
        $range,
        bool $practiceHasCcmPlus,
        $totalTime,
        bool $isBehavioral,
        bool $isPcm
    ) {
        $rate = 0.0;

        switch ($rangeKey) {
            case 0:
                if ($isPcm) {
                    if ($totalTime >= self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM) {
                        $rate = $nurseInfo->visit_fee;
                    }
                } else {
                    if ($totalTime >= self::MONTHLY_TIME_TARGET_IN_SECONDS) {
                        $rate = $nurseInfo->visit_fee;
                    }
                }
                break;
            case 1:
                if ( ! ($isPcm || $isBehavioral) && $practiceHasCcmPlus && $totalTime >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
                    $rate = $nurseInfo->visit_fee_2;
                }
                break;
            case 2:
                if ( ! ($isPcm || $isBehavioral) && $practiceHasCcmPlus && $totalTime >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS) {
                    $rate = $nurseInfo->visit_fee_3;
                }
                break;
            default:
                break;
        }

        if (0.0 === $rate) {
            return null;
        }

        $nurseCcmPercentageInRange = $this->getNurseTimePercentageAllocationInRange($nurseInfo->id, $range,
            $isPcm);
        if (0 === $nurseCcmPercentageInRange) {
            return null;
        }

        return $nurseCcmPercentageInRange * $rate;
    }

    /**
     * @param Nurse $nurseInfo
     * @param Collection $patientCareRateLogs
     *
     * @return PatientPayCalculationResult
     */
    private
    function getPayForPatientWithDefaultAlgo(
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

        $highRates = [$towardsCCm * $nurseInfo->high_rate];
        $lowRates  = [$afterCCm * $nurseInfo->low_rate];

        return PatientPayCalculationResult::withHighLowRates($highRates, $lowRates);
    }

    private
    function isNewNursePayAlgoEnabled()
    {
        return NurseCcmPlusConfig::enabledForAll();
    }

    private
    function isNewNursePayAltAlgoEnabledForUser(
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

    private
    function practiceHasCcmPlusCode(
        Practice $practice
    ) {
        return Cache::store('array')->rememberForever("ccm_plus_$practice->id", function () use ($practice) {
            return $practice->hasCCMPlusServiceCode();
        });
    }

    private
    function practiceHasPcmPlusCode(
        Practice $practice
    ) {
        return Cache::store('array')->rememberForever("pcm_$practice->id", function () use ($practice) {
            return $practice->hasServiceCode(ChargeableService::PCM);
        });
    }

    private
    function separateTimeAccruedInRanges(
        Collection $patientCareRateLogs,
        bool $isPcm = false
    ) {
        /**
         * CCM (ccm plus)
         * 0 => 0-20
         * 1 => 20-40
         * 2 => 40-60
         * 3 => 60+.
         */
        $ccmRanges = [
            0 => [],
            1 => [],
            2 => [],
            3 => [],
        ];

        /**
         * BHI
         * 0 => 0-20
         * 1 => 20+
         */
        $bhiRanges = [
            0 => [],
            1 => [],
        ];

        /**
         * PCM
         * 0 => 0-30
         * 1 => 30+
         */
        $pcmRanges = [
            0 => [],
            1 => [],
        ];


        $patientCareRateLogs->each(function ($e) use (&$ccmRanges, &$pcmRanges, &$bhiRanges, $isPcm) {
            $nurseInfoId      = $e['nurse_id'];
            $isSuccessfulCall = $e['is_successful_call'];
            $isBehavioral     = $e['is_behavioral'];
            $duration         = $e['increment'];
            $totalTimeBefore  = $e['time_before'];
            $totalTimeAfter   = $totalTimeBefore + $duration;

            //ccm + bhi
            $add_to_accrued_towards_20 = 0;
            $add_to_accrued_after_20   = 0;
            $add_to_accrued_after_40   = 0;
            $add_to_accrued_after_60   = 0;

            //pcm
            $add_to_accrued_towards_30 = 0;
            $add_to_accrued_after_30   = 0;

            //patient was above target before storing activity
            $was_above_20 = $totalTimeBefore >= self::MONTHLY_TIME_TARGET_IN_SECONDS;
            $was_above_30 = $totalTimeBefore >= self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM;
            $was_above_40 = $totalTimeBefore >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
            $was_above_60 = $totalTimeBefore >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS;

            //patient went above target after activity
            $is_above_20 = $totalTimeAfter >= self::MONTHLY_TIME_TARGET_IN_SECONDS;
            $is_above_30 = $totalTimeAfter >= self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM;
            $is_above_40 = $totalTimeAfter >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
            $is_above_60 = $totalTimeAfter >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS;

            //ccm + bhi
            if ($was_above_60) {
                $add_to_accrued_after_60 = $duration;
            } elseif ($was_above_40) {
                if ($is_above_60) {
                    $add_to_accrued_after_60 = $totalTimeAfter - self::MONTHLY_TIME_TARGET_3X_IN_SECONDS;
                    $add_to_accrued_after_40 = self::MONTHLY_TIME_TARGET_3X_IN_SECONDS - $totalTimeBefore;
                } else {
                    $add_to_accrued_after_40 = $duration;
                }
            } elseif ($was_above_20) {
                if ($is_above_40) {
                    $add_to_accrued_after_40 = $totalTimeAfter - self::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
                    $add_to_accrued_after_20 = self::MONTHLY_TIME_TARGET_2X_IN_SECONDS - $totalTimeBefore;
                } else {
                    $add_to_accrued_after_20 = $duration;
                }
            } else {
                if ($is_above_20) {
                    $add_to_accrued_after_20   = $totalTimeAfter - self::MONTHLY_TIME_TARGET_IN_SECONDS;
                    $add_to_accrued_towards_20 = self::MONTHLY_TIME_TARGET_IN_SECONDS - $totalTimeBefore;
                } else {
                    $add_to_accrued_towards_20 = $duration;
                }
            }

            if ( ! $isBehavioral && $isPcm) {
                if ($was_above_30) {
                    $add_to_accrued_after_30 = $duration;
                } else {
                    if ($is_above_30) {
                        $add_to_accrued_after_30   = $totalTimeAfter - self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM;
                        $add_to_accrued_towards_30 = self::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM - $totalTimeBefore;
                    } else {
                        $add_to_accrued_towards_30 = $duration;
                    }
                }
            }

            $var = 'ccmRanges';
            if ($isBehavioral) {
                $var = 'bhiRanges';
            } else if ($isPcm) {
                $var = 'pcmRanges';
            }

            // we can have ccm only, pcm only, bhi only
            // ccm + bhi, pcm + bhi, but we cannot have ccm + pcm
            if (($isPcm && $isBehavioral || ! $isPcm) && $add_to_accrued_towards_20) {
                ${$var}[0][$nurseInfoId] = $this->getEntryForRange(
                    ${$var},
                    0,
                    $nurseInfoId,
                    $add_to_accrued_towards_20,
                    $isSuccessfulCall
                );
            }

            if (($isPcm && $isBehavioral || ! $isPcm) && $add_to_accrued_after_20) {
                ${$var}[1][$nurseInfoId] = $this->getEntryForRange(
                    ${$var},
                    1,
                    $nurseInfoId,
                    $add_to_accrued_after_20,
                    $isSuccessfulCall
                );
            }

            if (($isPcm && $isBehavioral || ! $isPcm) && $add_to_accrued_after_40) {
                $index                        = $isBehavioral
                    ? 1
                    : 2;
                ${$var}[$index][$nurseInfoId] = $this->getEntryForRange(
                    ${$var},
                    $index,
                    $nurseInfoId,
                    $add_to_accrued_after_40,
                    $isSuccessfulCall
                );
            }

            if (($isPcm && $isBehavioral || ! $isPcm) && $add_to_accrued_after_60) {
                $index                        = $isBehavioral
                    ? 1
                    : 3;
                ${$var}[$index][$nurseInfoId] = $this->getEntryForRange(
                    ${$var},
                    $index,
                    $nurseInfoId,
                    $add_to_accrued_after_60,
                    $isSuccessfulCall
                );
            }

            if ($isPcm && ! $isBehavioral && $add_to_accrued_towards_30) {
                ${$var}[0][$nurseInfoId] = $this->getEntryForRange(
                    ${$var},
                    0,
                    $nurseInfoId,
                    $add_to_accrued_towards_30,
                    $isSuccessfulCall
                );
            }

            if ($isPcm && ! $isBehavioral && $add_to_accrued_after_30) {
                ${$var}[1][$nurseInfoId] = $this->getEntryForRange(
                    ${$var},
                    1,
                    $nurseInfoId,
                    $add_to_accrued_after_30,
                    $isSuccessfulCall
                );
            }
        });

        return [
            'ccm' => $ccmRanges,
            'bhi' => $bhiRanges,
            'pcm' => $pcmRanges,
        ];
    }
}

class CalculationResult
{
    /** @var bool New CCM Plus Algo from Jan 2020 */
    public $ccmPlusAlgoEnabled;

    /** @var bool Option 1 (alt algo - visit fee based if true, Option 2 otherwise */
    public $altAlgoEnabled;

    /** @var array $visits A 2d array, key[patient id] => value[array]. The value array is key[range] => value[pay] */
    public $visits;

    /** @var array $bhiVisits A 2d array, key[patient id] => value[array]. The value array is key[range] => value[pay] */
    public $bhiVisits;

    /** @var array $pcmVisits A 2d array, key[patient id] => value[array]. The value array is key[range] => value[pay] */
    public $pcmVisits;

    /** @var int Indicates number of visits in case {@link $altAlgoEnabled} is true */
    public $visitsCount;

    /** @var float Total pay */
    public $totalPay;

    public function __construct(
        bool $ccmPlusAlgoEnabled,
        bool $altAlgoEnabled,
        array $visits,
        array $bhiVisits,
        array $pcmVisits,
        float $totalPay
    ) {
        $this->ccmPlusAlgoEnabled = $ccmPlusAlgoEnabled;
        $this->altAlgoEnabled     = $altAlgoEnabled;
        $this->visits             = $visits;
        $this->bhiVisits          = $bhiVisits;
        $this->pcmVisits          = $pcmVisits;

        // 1. Flatten list of visits -> so list of visits per patient changes to list of visits
        // 2. Filter out entries with 0 -> visits that resulted in 0 compensation; probably cz of ccm time but no call, but call from other care coach
        $this->visitsCount = collect($visits)->flatten()->filter()->count();
        $this->visitsCount += collect($bhiVisits)->flatten()->filter()->count();
        $this->visitsCount += collect($pcmVisits)->flatten()->filter()->count();

        $this->totalPay = $totalPay;
    }
}

class PatientPayCalculationResult
{

    /** @var array In case of visit fee payment, [range(key), payment(value)] */
    public $visits;

    /** @var array In case of visit fee payment, [range(key), payment(value)] */
    public $bhiVisits;

    /** @var array In case of visit fee payment, [range(key), payment(value)] */
    public $pcmVisits;

    /** @var array In case of variable pay payment, array of high rate payments */
    public $highRates;

    /** @var array In case of variable pay payment, array of low rate payments */
    public $lowRates;

    /** @var float */
    public $pay;

    /**
     * PatientPayCalculationResult constructor.
     *
     * @param array $visits
     */
    private function __construct()
    {

    }

    public static function withVisits(array $visits, array $bhiVisits, array $pcmVisits)
    {
        $instance            = new self();
        $instance->visits    = $visits;
        $instance->bhiVisits = $bhiVisits;
        $instance->pcmVisits = $pcmVisits;
        $instance->pay       = collect($visits)->sum() + collect($bhiVisits)->sum();

        return $instance;
    }

    public static function withHighLowRates($highRates, $lowRates)
    {
        $instance            = new self();
        $instance->highRates = $highRates;
        $instance->lowRates  = $lowRates;
        $instance->pay       = collect($highRates)->sum() + collect($lowRates)->sum();

        return $instance;
    }


}