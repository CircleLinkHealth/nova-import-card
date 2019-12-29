<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\User;
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

    public function __construct(array $nurseInfoIds, Carbon $startDate, Carbon $endDate)
    {
        $this->nurseInfoIds = $nurseInfoIds;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
    }

    public function calculate(User $nurse, Collection $careRateLogs)
    {
        $nurseUserId   = $nurse->id;
        $nurseVisitFee = $nurse->nurseInfo->visit_fee;
        $nurseHighRate = $nurse->nurseInfo->high_rate;
        $nurseLowRate  = $nurse->nurseInfo->low_rate;

        $perPatient = $careRateLogs->mapToGroups(function ($e) {
            return [$e['patient_user_id'] => $e];
        });

        $totalPay = 0.0;
        $perPatient->each(function (Collection $p) use (
            &$totalPay,
            $nurseUserId,
            $nurseVisitFee,
            $nurseHighRate,
            $nurseLowRate
        ) {
            $payForPatient = 0.0;

            $patientUserId = $p->first()->patient_user_id;
            $patient = User::with('primaryPractice.chargeableServices')->find($patientUserId);
            if ($this->isNewNursePayAlgoEnabled($nurseUserId) && $patient->primaryPractice->hasCCMPlusServiceCode()) {
                $totalCcm = $patient->patientSummaryForMonth($this->startDate)->ccm_time;
                $ranges = $this->separateTimeAccruedInRanges($p);
                if ($this->isAltAlgoEnabled()) {
                    $payForPatient = $this->getPayForPatientWithCcmPlusAltAlgo(
                        $nurseHighRate,
                        $nurseLowRate,
                        $totalCcm,
                        $ranges
                    );
                } else {
                    $payForPatient = $this->getPayForPatientWithCcmPlusAlgo($nurseVisitFee, $totalCcm, $ranges);
                }
            } else {
                $payForPatient = $this->getPayForPatientWithDefaultAlgo($nurseHighRate, $nurseLowRate, $p);
            }

            $totalPay += $payForPatient;
        });

        return round($totalPay, 2);
    }

    public function getForNurses()
    {
        return NurseCareRateLog::whereIn('nurse_id', $this->nurseInfoIds)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();
    }

    private function getEntryForRange($ranges, $index, $newDuration, $successfulCall)
    {
        $prev = $ranges[$index];

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

    private function getPayForPatientWithCcmPlusAlgo($nurseVisitFee, $totalCcm, $ranges)
    {
        $result = 0.0;

        //if total ccm is greater than the range, then we can pay that range
        foreach ($ranges as $key => $value) {
            if ( ! array_key_exists('has_successful_call', $value) || ! $value['has_successful_call']) {
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

            $maxForRange     = 1200;
            $nurseCcmInRange = $value['duration'];
            $result += ($nurseCcmInRange / $maxForRange) * $nurseVisitFee;
        }

        return $result;
    }

    private function getPayForPatientWithCcmPlusAltAlgo($nurseHighRate, $nurseLowRate, $totalCcm, $ranges)
    {
        $result = 0.0;

        //nurse must have at least a successful call in order for us to pay
        $hasSuccessfulCall = collect($ranges)
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

        foreach ($ranges as $key => $value) {
            if ( ! array_key_exists('has_successful_call', $value) || ! $value['has_successful_call']) {
                continue;
            }

            switch ($key) {
                case 0:
                    $shouldPayHighRate = $totalCcm >= self::MONTHLY_TIME_TARGET_IN_SECONDS;
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

    private function getPayForPatientWithDefaultAlgo($nurseHighRate, $nurseLowRate, Collection $p)
    {
        $towardsCCm = $p->where('ccm_type', '=', 'accrued_towards_ccm')->sum('increment');
        $towardsCCm = $towardsCCm / self::HOUR_IN_SECONDS;
        $afterCCm   = $p->where('ccm_type', '=', 'accrued_after_ccm')->sum('increment');
        $afterCCm   = $afterCCm / self::HOUR_IN_SECONDS;

        return ($towardsCCm * $nurseHighRate) + ($afterCCm * $nurseLowRate);
    }

    private function isAltAlgoEnabled()
    {
        return 'option_1' !== config('app.nurse_ccm_plus_pay_algo');
    }

    private function isNewNursePayAlgoEnabled($nurseUserId)
    {
        if (config('app.nurse_ccm_plus_enabled_for_all')) {
            return true;
        }

        $enabledForUserIds = config('app.nurse_ccm_plus_enabled_for_user_ids');
        if ($enabledForUserIds) {
            $userIds = explode(',', $enabledForUserIds);

            return in_array($nurseUserId, $userIds);
        }

        return false;
    }

    private function separateTimeAccruedInRanges(Collection $entries)
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

        $entries->each(function ($e) use (&$ranges) {
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
                $ranges[0] = $this->getEntryForRange($ranges, 0, $add_to_accrued_towards_20, $e['is_successful_call']);
            }

            if ($add_to_accrued_after_20) {
                $ranges[1] = $this->getEntryForRange($ranges, 1, $add_to_accrued_after_20, $e['is_successful_call']);
            }

            if ($add_to_accrued_after_40) {
                $ranges[2] = $this->getEntryForRange($ranges, 2, $add_to_accrued_after_40, $e['is_successful_call']);
            }

            if ($add_to_accrued_after_60) {
                $ranges[3] = $this->getEntryForRange($ranges, 3, $add_to_accrued_after_60, $e['is_successful_call']);
            }
        });

        return $ranges;
    }
}
