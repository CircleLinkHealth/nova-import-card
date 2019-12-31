<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use Carbon\Carbon;
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
        $nurseUserId   = $nurse->id;
        $nurseInfoId   = $nurse->nurseInfo->id;
        $nurseVisitFee = $nurse->nurseInfo->visit_fee;
        $nurseHighRate = $nurse->nurseInfo->high_rate;
        $nurseLowRate  = $nurse->nurseInfo->low_rate;

        $careRateLogs = $this->getForNurses();

        $perPatient = $careRateLogs->mapToGroups(function ($e) {
            return [$e['patient_user_id'] => $e];
        });

        $totalPay = 0.0;
        $perPatient->each(function (Collection $patientCareRateLogs) use (
            &$totalPay,
            $nurseUserId,
            $nurseInfoId,
            $nurseVisitFee,
            $nurseHighRate,
            $nurseLowRate
        ) {
            $payForPatient = 0.0;

            $patientUserId = $patientCareRateLogs->first()->patient_user_id;
            if ( ! $patientUserId) {
                //we reach here when we have old records
                $payForPatient = $this->getPayForPatientWithDefaultAlgo(
                    $nurseInfoId,
                    $nurseHighRate,
                    $nurseLowRate,
                    $patientCareRateLogs
                );
            } else {
                $patient = User::with('primaryPractice.chargeableServices')->find($patientUserId);
                if ($this->isNewNursePayAlgoEnabled() && $this->practiceHasCcmPlusCode($patient->primaryPractice)) {
                    //we reach here if new algo is enabled
                    $totalCcm = $patient->patientSummaryForMonth($this->startDate)->ccm_time;
                    $ranges = $this->separateTimeAccruedInRanges($patientCareRateLogs);

                    //testing alternative algorithm
                    if ($this->isNewNursePayAltAlgoEnabledForUser($nurseUserId)) {
                        $payForPatient = $this->getPayForPatientWithCcmPlusAltAlgo(
                            $nurseInfoId,
                            $nurseVisitFee,
                            $totalCcm,
                            $ranges
                        );
                    } else {
                        //new algorithm for ccm plus codes
                        $payForPatient = $this->getPayForPatientWithCcmPlusAlgo(
                            $nurseInfoId,
                            $nurseHighRate,
                            $nurseLowRate,
                            $totalCcm,
                            $ranges
                        );
                    }
                } else {
                    $payForPatient = $this->getPayForPatientWithDefaultAlgo(
                        $nurseInfoId,
                        $nurseHighRate,
                        $nurseLowRate,
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

        $this->nurseCareRateLogs = NurseCareRateLog::whereIn('nurse_id', $this->nurseInfoIds)
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
     * This depends on the requirement that nurse must have a successful call in that range.
     *
     * @param $nurseInfoId
     * @param $range
     */
    private function getNurseTimeAllocationInRange($nurseInfoId, $range): int
    {
        $filtered = collect($range)->filter(function ($f) {
            return $f['has_successful_call'];
        });

        if ( ! $filtered->has($nurseInfoId)) {
            return 0;
        }

        if (1 === $filtered->count()) {
            return self::MONTHLY_TIME_TARGET_IN_SECONDS;
        }

        return $filtered[$nurseInfoId]['duration'];
    }

    /**
     * 0. Must have at least one successful call.
     * 1. High rate for 0-20 range.
     * 2. High rate for 20-40 range only if completed.
     * 3. High rate for 40-60 range only if completed.
     * 4. Low rate otherwise.
     *
     * @param $nurseInfoId
     * @param $nurseHighRate
     * @param $nurseLowRate
     * @param $totalCcm
     * @param $ranges
     *
     * @return float|int
     */
    private function getPayForPatientWithCcmPlusAlgo($nurseInfoId, $nurseHighRate, $nurseLowRate, $totalCcm, $ranges)
    {
        $result = 0.0;

        $rangesForNurseOnly = collect($ranges)->map(function ($r) use ($nurseInfoId) {
            return array_key_exists($nurseInfoId, $r)
                ? $r[$nurseInfoId]
                : [];
        });

        //nurse must have at least a successful call in any range in order for us to pay
        $hasSuccessfulCall = $rangesForNurseOnly
            ->filter(function ($f) {
                if ( ! array_key_exists('has_successful_call', $f)) {
                    return false;
                }

                return $f['has_successful_call'];
            })
            ->isNotEmpty();

        if ( ! $hasSuccessfulCall) {
            return $result;
        }

        foreach ($rangesForNurseOnly as $key => $value) {
            if ( ! array_key_exists('duration', $value)) {
                continue;
            }

            switch ($key) {
                case 0:
                    //0-20 always pays high rate
                    $shouldPayHighRate = true;
                    break;
                case 1:
                    $shouldPayHighRate = $totalCcm >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
                    break;
                case 2:
                    $shouldPayHighRate = $totalCcm >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS;
                    break;
                default:
                    $shouldPayHighRate = false;
                    break;
            }

            $nurseCcmInRange = $value['duration'] / self::HOUR_IN_SECONDS;
            $result += $nurseCcmInRange * ($shouldPayHighRate
                    ? $nurseHighRate
                    : $nurseLowRate);
        }

        return $result;
    }

    private function getPayForPatientWithCcmPlusAltAlgo($nurseInfoId, $nurseVisitFee, $totalCcm, $ranges)
    {
        $result = 0.0;

        //if total ccm is greater than the range, then we can pay that range
        foreach ($ranges as $key => $value) {
            if (0 === sizeof($value)) {
                continue;
            }

            switch ($key) {
                case 0:
                    $shouldPay = $totalCcm >= self::MONTHLY_TIME_TARGET_IN_SECONDS;
                    break;
                case 1:
                    $shouldPay = $totalCcm >= self::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
                    break;
                case 2:
                    $shouldPay = $totalCcm >= self::MONTHLY_TIME_TARGET_3X_IN_SECONDS;
                    break;
                default:
                    $shouldPay = false;
                    break;
            }

            if ( ! $shouldPay) {
                continue;
            }

            $maxForRange     = self::MONTHLY_TIME_TARGET_IN_SECONDS;
            $nurseCcmInRange = $this->getNurseTimeAllocationInRange($nurseInfoId, $value);
            $result += $nurseCcmInRange > 0
                ? ($nurseCcmInRange / $maxForRange) * $nurseVisitFee
                : 0.0;
        }

        return $result;
    }

    private function getPayForPatientWithDefaultAlgo(
        $nurseInfoId,
        $nurseHighRate,
        $nurseLowRate,
        Collection $patientCareRateLogs
    ) {
        $towardsCCm = $patientCareRateLogs
            ->where('nurse_id', '=', $nurseInfoId)
            ->where('ccm_type', '=', 'accrued_towards_ccm')
            ->sum('increment');
        $towardsCCm = $towardsCCm / self::HOUR_IN_SECONDS;

        $afterCCm = $patientCareRateLogs
            ->where('nurse_id', '=', $nurseInfoId)
            ->where('ccm_type', '=', 'accrued_after_ccm')
            ->sum('increment');
        $afterCCm = $afterCCm / self::HOUR_IN_SECONDS;

        return ($towardsCCm * $nurseHighRate) + ($afterCCm * $nurseLowRate);
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
