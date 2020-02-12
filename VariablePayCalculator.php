<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Config\NurseCcmPlusConfig;
use Illuminate\Support\Collection;

class VariablePayCalculator
{
    const HOUR_IN_SECONDS = 3600;
    const MONTHLY_TIME_TARGET_2X_IN_SECONDS = 2400;
    const MONTHLY_TIME_TARGET_3X_IN_SECONDS = 3600;
    const MONTHLY_TIME_TARGET_IN_SECONDS = 1200;

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

    /**
     * Cache variable, checks if practice has G2058 code enabled.
     *
     * @var array|null
     */
    private $practiceCcmPlusEnabled = [];

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
        $ccmPlusAlgoEnabled = $this->isNewNursePayAlgoEnabled();
        $visitFeeBased      = $ccmPlusAlgoEnabled && $this->isNewNursePayAltAlgoEnabledForUser($nurseUserId);

        $perPatient->each(function (Collection $patientCareRateLogs) use (
            &$totalPay,
            &$visits,
            &$bhiVisits,
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
        });

        return new CalculationResult($ccmPlusAlgoEnabled, $visitFeeBased, $visits, $bhiVisits, $totalPay);
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

    private function getEntryForRange($ranges, $index, $nurseInfoId, $newDuration, $successfulCall, $isBehavioral)
    {
        $type  = $isBehavioral
            ? 'bhi'
            : 'ccm';
        $range = $ranges[$index];
        $prev  = null;
        if (array_key_exists($nurseInfoId, $range)) {
            $prev = $ranges[$index][$nurseInfoId][$type];
        }

        $duration          = $newDuration;
        $hasSuccessfulCall = $successfulCall;
        if ($prev) {
            if ( ! $hasSuccessfulCall && $prev['has_successful_call']) {
                $hasSuccessfulCall = true;
            }

            $duration += $prev['duration'];
        }

        $result = [
            'duration'            => $duration,
            'has_successful_call' => $hasSuccessfulCall,
        ];

        if ($isBehavioral) {
            return [
                'bhi' => $result,
                'ccm' => $ranges[$index][$nurseInfoId]['ccm'] ?? ['duration' => 0, 'has_successful_call' => 0],
            ];
        } else {
            return [
                'bhi' => $ranges[$index][$nurseInfoId]['bhi'] ?? ['duration' => 0, 'has_successful_call' => 0],
                'ccm' => $result,
            ];
        }
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
     *
     * @return float
     */
    private function getNurseTimePercentageAllocationInRange($nurseInfoId, $range, bool $isBehavioral): float
    {
        $elqRange = collect($range)->map(function ($r) use ($isBehavioral) {
            return $isBehavioral
                ? $r['bhi']
                : $r['ccm'];
        });

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
            return $elqRange[$nurseInfoId]['duration'] / self::MONTHLY_TIME_TARGET_IN_SECONDS;
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

        $practiceHasCcmPlus = $this->practiceHasCcmPlusCode($patient->primaryPractice);
        /** @var PatientMonthlySummary $patientSummary */
        $patientSummary = $patient->patientSummaryForMonth($this->startDate);
        $totalCcm       = $patientSummary->ccm_time;
        $totalBhi       = $patientSummary->bhi_time;
        $ranges         = $this->separateTimeAccruedInRanges($patientCareRateLogs);
        if ($visitFeeBased) {
            return $this->getPayForPatientWithCcmPlusAltAlgo(
                $nurseInfo,
                $patientUserId,
                $totalCcm,
                $totalBhi,
                $ranges,
                $practiceHasCcmPlus
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

        $rangesForNurseOnly = collect($ranges)->map(function ($r) use ($nurseInfoId) {
            return array_key_exists($nurseInfoId, $r)
                ? $r[$nurseInfoId]
                : [];
        });

        $hasSuccessfulCall = $rangesForNurseOnly
            ->filter(function ($f) {
                if (array_key_exists('ccm', $f)) {
                    $val = $f['ccm'];

                    if ($val['has_successful_call']) {
                        return true;
                    }
                }

                if (array_key_exists('bhi', $f)) {
                    $val = $f['bhi'];

                    if ($val['has_successful_call']) {
                        return true;
                    }
                }

                return false;
            })
            ->isNotEmpty();

        foreach ($rangesForNurseOnly as $key => $value) {

            if (0 === sizeof($value)) {
                continue;
            }

            $ccmInRange = $value['ccm'];
            $ccmPay     = $this->getVariableRatePayForRange($nurseInfo, $key, $ccmInRange, $hasSuccessfulCall,
                $practiceHasCcmPlus, $totalCcm, false);
            if ($ccmPay) {
                if ($ccmPay['rate'] === $nurseInfo->low_rate) {
                    $lowRates[] = $ccmPay['pay'];
                } else {
                    $highRates[] = $ccmPay['pay'];
                }
            }

            $bhiInRange = $value['bhi'];
            $bhiPay     = $this->getVariableRatePayForRange($nurseInfo, $key, $bhiInRange, $hasSuccessfulCall,
                $practiceHasCcmPlus, $totalBhi, true);
            if ($bhiPay) {
                if ($bhiPay['rate'] === $nurseInfo->low_rate) {
                    $lowRates[] = $bhiPay['pay'];
                } else {
                    $highRates[] = $bhiPay['pay'];
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
     * @param $ranges
     * @param bool $practiceHasCcmPlus
     *
     * @return PatientPayCalculationResult
     */
    private function getPayForPatientWithCcmPlusAltAlgo(
        Nurse $nurseInfo,
        $patientId,
        $totalCcm,
        $totalBhi,
        $ranges,
        $practiceHasCcmPlus = false
    ) {
        $visits    = [];
        $bhiVisits = [];

        $patientHasAtLeastOneSuccessfulCall = collect($ranges)->filter(function ($f) {
            return collect($f)->filter(function ($f2) {

                if (array_key_exists('ccm', $f2)) {
                    if ($f2['ccm']['has_successful_call']) {
                        return true;
                    }
                }

                if (array_key_exists('bhi', $f2)) {
                    if ($f2['bhi']['has_successful_call']) {
                        return true;
                    }
                }

                return false;
            })->isNotEmpty();
        })->isNotEmpty();

        if ( ! $patientHasAtLeastOneSuccessfulCall) {
            return PatientPayCalculationResult::withVisits($visits, $bhiVisits);
        }

        // CPM-1997
        // If only 1 billable event, the RN(s) with successful call(s) split VF proportionally,
        // any other RNs spending time without a successful call get 0%.
        $noOfBillableEvents = 0;
        if ($totalCcm >= self::MONTHLY_TIME_TARGET_IN_SECONDS) {
            $noOfBillableEvents++;
        }
        if ($practiceHasCcmPlus && $totalCcm >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
            $noOfBillableEvents++;
        }
        if ($practiceHasCcmPlus && $totalCcm >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS) {
            $noOfBillableEvents++;
        }
        $isBhiBillable = $totalBhi >= self::MONTHLY_TIME_TARGET_IN_SECONDS;
        if ($isBhiBillable) {
            $noOfBillableEvents++;
        }

        if ($noOfBillableEvents === 1) {
            $pay = $this->getVisitFeePayForOneBillableEvent($nurseInfo, $patientId, $ranges, $isBhiBillable);
            if ( ! $isBhiBillable) {
                $visits[0] = $pay;
            } else {
                $bhiVisits[0] = $pay;
            }
        } else {
            //if total ccm is greater than the range, then we can pay that range
            foreach ($ranges as $key => $value) {

                $ccmPay = $this->getVisitFeePayForRange($nurseInfo, $key, $value, $practiceHasCcmPlus, $totalCcm,
                    false);
                if ($ccmPay) {
                    $visits[$key] = $ccmPay;
                }

                $bhiPay = $this->getVisitFeePayForRange($nurseInfo, $key, $value, $practiceHasCcmPlus, $totalBhi,
                    true);
                if ($bhiPay) {
                    $bhiVisits[$key] = $bhiPay;
                }
            }
        }

        return PatientPayCalculationResult::withVisits($visits, $bhiVisits);
    }

    private function getVisitFeePayForOneBillableEvent(Nurse $nurseInfo, $patientId, $ranges, $isBehavioral)
    {
        $elqRange = collect($ranges)->map(function ($r) use ($isBehavioral) {
            return collect($r)->map(function ($r2) use ($isBehavioral) {
                return $isBehavioral
                    ? $r2['bhi']
                    : $r2['ccm'];
            });
        });

        // calculate time for each nurse that has a successful call
        $nurseTimes = collect();
        $elqRange->each(function (Collection $f) use ($nurseTimes) {
            return $f->each(function ($f2, $key) use ($nurseTimes) {
                if ( ! $f2['has_successful_call']) {
                    return;
                }
                $current = $nurseTimes->get($key, 0);
                $nurseTimes->put($key, $current + $f2['duration']);
            });
        });

        $sumOfAllTime = $nurseTimes->sum();
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
        $nurseTime = $nurseTimes->get($nurseInfo->id, 0);

        return ($nurseTime / $sumOfAllTime) * $nurseInfo->visit_fee;
    }

    private function getVisitFeePayForRange(
        Nurse $nurseInfo,
        $rangeKey,
        $range,
        bool $practiceHasCcmPlus,
        $totalTime,
        bool $isBehavioral
    ) {
        $rate = 0.0;

        switch ($rangeKey) {
            case 0:
                if ($totalTime >= self::MONTHLY_TIME_TARGET_IN_SECONDS) {
                    $rate = $nurseInfo->visit_fee;
                }
                break;
            case 1:
                if ( ! $isBehavioral && $practiceHasCcmPlus && $totalTime >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
                    $rate = $nurseInfo->visit_fee_2;
                }
                break;
            case 2:
                if ( ! $isBehavioral && $practiceHasCcmPlus && $totalTime >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS) {
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
            $isBehavioral);
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

        $highRates = [$towardsCCm * $nurseInfo->high_rate];
        $lowRates  = [$afterCCm * $nurseInfo->low_rate];

        return PatientPayCalculationResult::withHighLowRates($highRates, $lowRates);
    }

    private function isNewNursePayAlgoEnabled()
    {
        return NurseCcmPlusConfig::enabledForAll();
    }

    private function isNewNursePayAltAlgoEnabledForUser($nurseUserId)
    {
        $enabledForUserIds = NurseCcmPlusConfig::altAlgoEnabledForUserIds();
        if ($enabledForUserIds) {
            return in_array($nurseUserId, $enabledForUserIds);
        }

        return false;
    }

    private function practiceHasCcmPlusCode(Practice $practice)
    {
        if (array_key_exists($practice->id, $this->practiceCcmPlusEnabled)) {
            return $this->practiceCcmPlusEnabled[$practice->id];
        }
        $this->practiceCcmPlusEnabled[$practice->id] = $practice->hasCCMPlusServiceCode();

        return $this->practiceCcmPlusEnabled[$practice->id];
    }

    private function separateTimeAccruedInRanges(Collection $patientCareRateLogs)
    {
        /**
         * 0 => 0-20
         * 1 => 20-40
         * 2 => 40-60
         * 3 => 60+.
         */
        $ranges = [
            0 => [],
            1 => [],
            2 => [],
            3 => [],
        ];

        $patientCareRateLogs->each(function ($e) use (&$ranges) {
            $nurseInfoId     = $e['nurse_id'];
            $isSuccssfulCall = $e['is_successful_call'];
            $isBehavioral    = $e['is_behavioral'];
            $duration        = $e['increment'];
            $totalTimeBefore = $e['time_before'];
            $totalTimeAfter  = $totalTimeBefore + $duration;

            $add_to_accrued_towards_20 = 0;
            $add_to_accrued_after_20   = 0;
            $add_to_accrued_after_40   = 0;
            $add_to_accrued_after_60   = 0;

            //patient was above target before storing activity
            $was_above_20 = $totalTimeBefore >= self::MONTHLY_TIME_TARGET_IN_SECONDS;
            $was_above_40 = $totalTimeBefore >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
            $was_above_60 = $totalTimeBefore >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS;

            //patient went above target after activity
            $is_above_20 = $totalTimeAfter >= self::MONTHLY_TIME_TARGET_IN_SECONDS;
            $is_above_40 = $totalTimeAfter >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
            $is_above_60 = $totalTimeAfter >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS;

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

            if ($add_to_accrued_towards_20) {
                $ranges[0][$nurseInfoId] = $this->getEntryForRange(
                    $ranges,
                    0,
                    $nurseInfoId,
                    $add_to_accrued_towards_20,
                    $isSuccssfulCall,
                    $isBehavioral
                );
            }

            if ($add_to_accrued_after_20) {
                $ranges[1][$nurseInfoId] = $this->getEntryForRange(
                    $ranges,
                    1,
                    $nurseInfoId,
                    $add_to_accrued_after_20,
                    $isSuccssfulCall,
                    $isBehavioral
                );
            }

            if ($add_to_accrued_after_40) {
                $ranges[2][$nurseInfoId] = $this->getEntryForRange(
                    $ranges,
                    2,
                    $nurseInfoId,
                    $add_to_accrued_after_40,
                    $isSuccssfulCall,
                    $isBehavioral
                );
            }

            if ($add_to_accrued_after_60) {
                $ranges[3][$nurseInfoId] = $this->getEntryForRange(
                    $ranges,
                    3,
                    $nurseInfoId,
                    $add_to_accrued_after_60,
                    $isSuccssfulCall,
                    $isBehavioral
                );
            }
        });

        return $ranges;
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

    /** @var int Indicates number of visits in case {@link $altAlgoEnabled} is true */
    public $visitsCount;

    /** @var float Total pay */
    public $totalPay;

    public function __construct(
        bool $ccmPlusAlgoEnabled,
        bool $altAlgoEnabled,
        array $visits,
        array $bhiVisits,
        float $totalPay
    ) {
        $this->ccmPlusAlgoEnabled = $ccmPlusAlgoEnabled;
        $this->altAlgoEnabled     = $altAlgoEnabled;
        $this->visits             = $visits;
        $this->bhiVisits          = $bhiVisits;

        // 1. Flatten list of visits -> so list of visits per patient changes to list of visits
        // 2. Filter out entries with 0 -> visits that resulted in 0 compensation; probably cz of ccm time but no call, but call from other care coach
        $this->visitsCount = collect($visits)->flatten()->filter()->count();
        $this->visitsCount += collect($bhiVisits)->flatten()->filter()->count();

        $this->totalPay = $totalPay;
    }
}

class PatientPayCalculationResult
{

    /** @var array In case of visit fee payment, [range(key), payment(value)] */
    public $visits;

    /** @var array In case of visit fee payment, [range(key), payment(value)] */
    public $bhiVisits;

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
    public function __construct()
    {

    }

    public static function withVisits(array $visits, array $bhiVisits)
    {
        $instance            = new self();
        $instance->visits    = $visits;
        $instance->bhiVisits = $bhiVisits;
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