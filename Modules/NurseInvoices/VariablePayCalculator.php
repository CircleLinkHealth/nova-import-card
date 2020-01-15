<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Config\NurseCcmPlusConfig;
use Illuminate\Support\Collection;

class VariablePayCalculator
{
    const HOUR_IN_SECONDS                   = 3600;
    const MONTHLY_TIME_TARGET_2X_IN_SECONDS = 2400;
    const MONTHLY_TIME_TARGET_3X_IN_SECONDS = 3600;
    const MONTHLY_TIME_TARGET_IN_SECONDS    = 1200;

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

    public function calculate(User $nurse)
    {
        $nurseUserId  = $nurse->id;
        $nurseInfo    = $nurse->nurseInfo;
        $careRateLogs = $this->getForNurses();

        $perPatient = $careRateLogs->mapToGroups(function ($e) {
            return [$e['patient_user_id'] => $e];
        });

        $totalPay = 0.0;
        $perPatient->each(function (Collection $patientCareRateLogs) use (
            &$totalPay,
            $nurseUserId,
            $nurseInfo
        ) {
            $payForPatient = 0.0;

            $patientUserId = $patientCareRateLogs->first()->patient_user_id;
            if ( ! $patientUserId) {
                //we reach here when we have old records
                $payForPatient = $this->getPayForPatientWithDefaultAlgo(
                    $nurseInfo,
                    $patientCareRateLogs
                );
            } else {
                if ($this->isNewNursePayAlgoEnabled()) {
                    $patient = User::with('primaryPractice.chargeableServices')->find($patientUserId);
                    $practiceHasCcmPlus = $this->practiceHasCcmPlusCode($patient->primaryPractice);
                    $totalCcm = $patient->patientSummaryForMonth($this->startDate)->ccm_time;
                    $ranges = $this->separateTimeAccruedInRanges($patientCareRateLogs);
                    if ($this->isNewNursePayAltAlgoEnabledForUser($nurseUserId)) {
                        $payForPatient = $this->getPayForPatientWithCcmPlusAltAlgo(
                            $nurseInfo,
                            $totalCcm,
                            $ranges,
                            $practiceHasCcmPlus
                        );
                    } else {
                        $payForPatient = $this->getPayForPatientWithCcmPlusAlgo(
                            $nurseInfo,
                            $totalCcm,
                            $ranges,
                            $practiceHasCcmPlus
                        );
                    }
                } else {
                    $payForPatient = $this->getPayForPatientWithDefaultAlgo(
                        $nurseInfo,
                        $patientCareRateLogs
                    );
                }
            }

            $totalPay += $payForPatient;
        });

        return round($totalPay, 2);
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
            if (array_key_exists('has_successful_call', $prev) && $prev['has_successful_call']) {
                $hasSuccessfulCall = true;
            }
            if (array_key_exists('duration', $prev)) {
                $duration += $prev['duration'];
            }
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
     *          both succesfull call -> 50% / 50%
     *          none with successfull call -> 50% / 50%
     *          only one with successful call -> 100% / 0%
     *
     * @param $nurseInfoId
     * @param $range
     */
    private function getNurseTimePercentageAllocationInRange($nurseInfoId, $range): int
    {
        $elqRange = collect($range);

        //only 1 RN, pay the full VF, regardless of calls
        if (1 === $elqRange->count()) {
            return $elqRange->has($nurseInfoId)
                ? self::MONTHLY_TIME_TARGET_IN_SECONDS
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
     * If practice has CCM Plus:
     * 1. High rate 1 for 0-20 range.
     * 2. High rate 2 for 20-40 range only if completed + have at least one successful call in any range.
     * 3. High rate 3 for 40-60 range only if completed + have at least one successful call in any range.
     * 4. Low rate otherwise.
     *
     * Else:
     * 1. High rate 1 for 0-20 range.
     * 2. Low rate for the rest.
     *
     * @param $totalCcm
     * @param $ranges
     * @param bool $practiceHasCcmPlus
     *
     * @return float|int
     */
    private function getPayForPatientWithCcmPlusAlgo(
        Nurse $nurseInfo,
        $totalCcm,
        $ranges,
        $practiceHasCcmPlus = false
    ) {
        $nurseInfoId = $nurseInfo->id;
        $result      = 0.0;

        $rangesForNurseOnly = collect($ranges)->map(function ($r) use ($nurseInfoId) {
            return array_key_exists($nurseInfoId, $r)
                ? $r[$nurseInfoId]
                : [];
        });

        $hasSuccessfulCall = $rangesForNurseOnly
            ->filter(function ($f) {
                if ( ! array_key_exists('has_successful_call', $f)) {
                    return false;
                }

                return $f['has_successful_call'];
            })
            ->isNotEmpty();

        foreach ($rangesForNurseOnly as $key => $value) {
            if ( ! array_key_exists('duration', $value)) {
                continue;
            }

            $rate = $nurseInfo->low_rate;

            switch ($key) {
                case 0:
                    //0-20 always pays high rate
                    $rate = $nurseInfo->high_rate;
                    break;
                case 1:
                    if ($hasSuccessfulCall &&
                        $practiceHasCcmPlus &&
                        $totalCcm >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
                        $rate = $nurseInfo->high_rate_2;
                    }
                    break;
                case 2:
                    if ($hasSuccessfulCall &&
                        $practiceHasCcmPlus &&
                        $totalCcm >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS) {
                        $rate = $nurseInfo->high_rate_3;
                    }
                    break;
                default:
                    $rate = 0;
                    break;
            }

            $nurseCcmInRange = $value['duration'] / self::HOUR_IN_SECONDS;
            $result += ($nurseCcmInRange * $rate);
        }

        return $result;
    }

    private function getPayForPatientWithCcmPlusAltAlgo(
        Nurse $nurseInfo,
        $totalCcm,
        $ranges,
        $practiceHasCcmPlus = false
    ) {
        $result = 0.0;

        $patientHasAtLeastOneSuccessfulCall = collect($ranges)->filter(function ($f) {
            return collect($f)->filter(function ($f2) {
                return $f2['has_successful_call'];
            })->isNotEmpty();
        })->isNotEmpty();

        //if total ccm is greater than the range, then we can pay that range
        foreach ($ranges as $key => $value) {
            if (0 === sizeof($value)) {
                continue;
            }

            $rate = 0.0;

            switch ($key) {
                case 0:
                    if ($totalCcm >= self::MONTHLY_TIME_TARGET_IN_SECONDS) {
                        $rate = $nurseInfo->visit_fee;
                    }
                    break;
                case 1:
                    if ($patientHasAtLeastOneSuccessfulCall &&
                        $practiceHasCcmPlus &&
                        $totalCcm >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS) {
                        $rate = $nurseInfo->visit_fee_2;
                    }
                    break;
                case 2:
                    if ($patientHasAtLeastOneSuccessfulCall &&
                        $practiceHasCcmPlus &&
                        $totalCcm >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS) {
                        $rate = $nurseInfo->visit_fee_3;
                    }
                    break;
                default:
                    break;
            }

            if (0.0 === $rate) {
                continue;
            }

            $nurseCcmPercentageInRange = $this->getNurseTimePercentageAllocationInRange($nurseInfo->id, $value);
            if (0 === $nurseCcmPercentageInRange) {
                continue;
            }

            $result += $nurseCcmPercentageInRange * $rate;
        }

        return $result;
    }

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

        return ($towardsCCm * $nurseInfo->high_rate) + ($afterCCm * $nurseInfo->low_rate);
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
            $nurseInfoId = $e['nurse_id'];
            $isSuccssfulCall = $e['is_successful_call'];
            $duration = $e['increment'];
            $totalTimeBefore = $e['time_before'];
            $totalTimeAfter = $totalTimeBefore + $duration;

            $add_to_accrued_towards_20 = 0;
            $add_to_accrued_after_20 = 0;
            $add_to_accrued_after_40 = 0;
            $add_to_accrued_after_60 = 0;

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
                    $add_to_accrued_after_20 = $totalTimeAfter - self::MONTHLY_TIME_TARGET_IN_SECONDS;
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
                    $isSuccssfulCall
                );
            }

            if ($add_to_accrued_after_20) {
                $ranges[1][$nurseInfoId] = $this->getEntryForRange(
                    $ranges,
                    1,
                    $nurseInfoId,
                    $add_to_accrued_after_20,
                    $isSuccssfulCall
                );
            }

            if ($add_to_accrued_after_40) {
                $ranges[2][$nurseInfoId] = $this->getEntryForRange(
                    $ranges,
                    2,
                    $nurseInfoId,
                    $add_to_accrued_after_40,
                    $isSuccssfulCall
                );
            }

            if ($add_to_accrued_after_60) {
                $ranges[3][$nurseInfoId] = $this->getEntryForRange(
                    $ranges,
                    3,
                    $nurseInfoId,
                    $add_to_accrued_after_60,
                    $isSuccssfulCall
                );
            }
        });

        return $ranges;
    }
}
