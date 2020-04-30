<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\ViewModels;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\VariablePayCalculator;
use Illuminate\Support\Collection;
use Spatie\ViewModels\ViewModel;

class Invoice extends ViewModel
{
    const DATE_FORMAT = 'jS M, Y';

    /**
     * @var Collection
     */
    public $aggregatedTotalTime;

    /**
     * @var bool Whether Option 1 (Alt Algo - Visit Fee based) algo is enabled or not
     */
    public $altAlgoEnabled;
    /**
     * @var float
     */
    public $baseSalary;

    /**
     * @var Collection A 2d array, key[patient id] => value[array]. The value array is key[range] => value[pay]
     */
    public $bhiVisits;

    /** @var bool New CCM Plus Algo from Jan 2020 */
    public $ccmPlusAlgoEnabled;
    /**
     * @var bool
     */
    public $changedToFixedRateBecauseItYieldedMore = false;

    /**
     * @var float the total pay with fixed rate algorithm
     */
    public $fixedRatePay = 0.0;
    public $nurseFullName;
    public $nurseHighRate;
    public $nurseHourlyRate;
    public $nurseLowRate;
    public $nurseVisitFee;

    /**
     * @var Collection A 2d array, key[patient id] => value[array]. The value array is key[range] => value[pay]
     */
    public $pcmVisits;

    /**
     * An array showing the total time per day.
     *
     * @return Collection
     */
    public $timePerDay;

    public $totalTimeAfterCcmInHours;
    public $totalTimeTowardsCcmInHours;
    /**
     * @var bool
     */
    public $variablePay;
    /**
     * @var float the total pay with variable rate algorithm
     */
    public $variableRatePay = 0.0;

    /**
     * @var Collection A 2d array, key[patient id] => value[array]. The value array is key[range] => value[pay]
     */
    public $visits;

    /**
     * @var int the total number of visits when option 1 algo is enabled
     */
    public $visitsCount;

    /**
     * An array showing the total visits per day.
     *
     * @return bool
     */
    public $visitsPerDayAvailable;
    /**
     * @var Carbon
     */
    protected $endDate;
    /**
     * @var int
     */
    protected $extraTime;

    /**
     * Do not make this methods available to classes consuming this ViewModel.
     *
     * @var array array
     */
    protected $ignore = ['user'];
    /**
     * @var Carbon
     */
    protected $startDate;

    /**
     * @var int
     */
    protected $totalSystemTime;
    /**
     * @var Collection
     */
    protected $variablePayForNurse;
    /**
     * @var mixed
     */
    private $bonus;
    /**
     * @var \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private $nurseExtras;
    /**
     * @var int|null
     */
    private $systemTimeInHours;
    /**
     * @var User
     */
    private $user;

    /** @var VariablePayCalculator */
    private $variablePayCalculator;

    /**
     * Invoice constructor.
     */
    public function __construct(
        User $user,
        Carbon $startDate,
        Carbon $endDate,
        Collection $aggregatedTotalTime,
        VariablePayCalculator $variablePayCalculator
    ) {
        $this->user                  = $user;
        $this->startDate             = $startDate;
        $this->endDate               = $endDate;
        $this->aggregatedTotalTime   = $aggregatedTotalTime->flatten();
        $this->variablePay           = (bool) $user->nurseInfo->is_variable_rate;
        $this->totalSystemTime       = $this->totalSystemTimeInSeconds();
        $this->bonus                 = $this->getBonus($user->nurseBonuses);
        $this->extraTime             = $this->getAddedDuration($user->nurseBonuses);
        $this->nurseHighRate         = $user->nurseInfo->high_rate;
        $this->nurseLowRate          = $user->nurseInfo->low_rate;
        $this->nurseHourlyRate       = $user->nurseInfo->hourly_rate;
        $this->nurseVisitFee         = $user->nurseInfo->visit_fee;
        $this->nurseFullName         = $user->getFullName();
        $this->variablePayCalculator = $variablePayCalculator;

        $variablePayMap = $this->variablePayCalculator->getForNurses();

        $variablePaySummary = $variablePayMap->filter(function ($f) use ($user) {
            return $f->nurse_id === $user->nurseInfo->id;
        });

        $this->variablePayForNurse = $variablePaySummary;

        $this->determineBaseSalary();

        $this->setViewModelVariables();
    }

    /**
     * Extra time in hours.
     *
     * @return float
     */
    public function addedTime()
    {
        return ceil($this->extraTime / 60);
    }

    /**
     * PAyable amount due to extra time.
     *
     * @return float|int
     */
    public function addedTimeAmount()
    {
        return $this->addedTime() * $this->user->nurseInfo->hourly_rate;
    }

    /**
     * Cash bonus total.
     *
     * @return float
     */
    public function bonus()
    {
        return ceil($this->bonus);
    }

    /**
     * The formatted end date.
     *
     * @return string
     */
    public function endDate()
    {
        return $this->endDate->format(self::DATE_FORMAT);
    }

    /**
     * Returns base salary using both "fixed rate" and "variable rate".
     * The 2 salaries are keyed using 'low', and 'high'.
     *
     * @return array|string
     */
    public function formattedBaseSalary()
    {
        $fixedRatePay     = number_format($this->fixedRatePay, 2);
        $fixedRateMessage = "\${$fixedRatePay} (Fixed Rate: \${$this->user->nurseInfo->hourly_rate}/hr).";

        if ( ! $this->variablePay && ! $this->changedToFixedRateBecauseItYieldedMore) {
            return $fixedRateMessage;
        }
        $high_rate   = $this->user->nurseInfo->high_rate;
        $high_rate_2 = $this->user->nurseInfo->high_rate_2;
        $high_rate_3 = $this->user->nurseInfo->high_rate_3;
        $low_rate    = $this->user->nurseInfo->low_rate;

        $variableRatePay = number_format($this->variableRatePay, 2);

        if ($this->ccmPlusAlgoEnabled) {
            if ($this->altAlgoEnabled) {
                $variableRateMessage = "\${$variableRatePay} (Visit-based: $this->visitsCount visits).";
            } else {
                $variableRateMessage = "\${$variableRatePay} (Variable Rate: \$$high_rate/hr, \$$high_rate_2/hr, \$$high_rate_3/hr or \$$low_rate/hr).";
            }
        } else {
            $variableRateMessage = "\${$variableRatePay} (Variable Rate: \$$high_rate/hr or \$$low_rate/hr).";
        }

        if ($this->variableRatePay > $this->fixedRatePay) {
            $result = [
                'high' => $variableRateMessage,
                'low'  => $fixedRateMessage,
            ];
        } else {
            $result = [
                'high' => $fixedRateMessage,
                'low'  => $variableRateMessage,
            ];
        }

        return $result;
    }

    /**
     * The formatted payable invoice amount.
     *
     * @return string
     */
    public function formattedInvoiceTotalAmount()
    {
        return '$'.number_format(round($this->invoiceTotalAmount(), 2), 2);
    }

    /**
     * Formatted system time in hh:mm.
     *
     * @return string
     */
    public function formattedSystemTime()
    {
        return minutesToHhMm($this->systemTimeInMinutes());
    }

    /**
     * Does the nurse have added time?
     *
     * @return bool
     */
    public function hasAddedTime()
    {
        return 0 != $this->extraTime;
    }

    /**
     * The total payable amount of the invoice. It includes all bonuses.
     *
     * @return float|int|mixed
     */
    public function invoiceTotalAmount()
    {
        return $this->baseSalary + $this->bonus + $this->addedTimeAmount();
    }

    /**
     * The formatted start date of the invoice.
     *
     * @return string
     */
    public function startDate()
    {
        return $this->startDate->format(self::DATE_FORMAT);
    }

    /**
     * Total system time in hours.
     *
     * @return float|int|null
     */
    public function systemTimeInHours()
    {
        if (is_null($this->systemTimeInHours)) {
            if (1 > $this->totalSystemTime) {
                $this->systemTimeInHours = 0;
            } elseif ($this->totalSystemTime <= 1800) {
                $this->systemTimeInHours = 0.5;
            } else {
                $this->systemTimeInHours = ceil($this->totalSystemTime / 3600);
            }
        }

        return $this->systemTimeInHours;
    }

    /**
     * Total system time in minutes.
     *
     * @return float|int
     */
    public function systemTimeInMinutes()
    {
        return $this->systemTimeInHours() * 60;
    }

    /**
     * The nurse's user model.
     *
     * @return User
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Determine base salary by comparing variable rate and fixed rate and choosing the highest.
     *
     * @return float|int|mixed
     */
    private function determineBaseSalary()
    {
        $this->fixedRatePay = $this->getFixedRatePay();

        if ( ! $this->variablePay) {
            $this->baseSalary = $this->fixedRatePay;
        } else {
            $this->variableRatePay = $this->getVariableRatePay();

            if ($this->fixedRatePay > $this->variableRatePay) {
                $this->baseSalary                             = $this->fixedRatePay;
                $this->changedToFixedRateBecauseItYieldedMore = true;
            } else {
                $this->baseSalary = $this->variableRatePay;
            }
        }

        return $this->baseSalary;
    }

    /**
     * Get the sum of nurse extra time in minutes.
     *
     * @param $nurseExtras
     *
     * @return mixed
     */
    private function getAddedDuration($nurseExtras)
    {
        return $nurseExtras
            ->where('unit', 'minutes')
            ->sum('value');
    }

    /**
     * Get the sum of the cash bonus in USD($).
     *
     * @param $nurseExtras
     *
     * @return mixed
     */
    private function getBonus($nurseExtras)
    {
        return $nurseExtras
            ->where('unit', 'usd')
            ->sum('value');
    }

    private function getFixedRatePay()
    {
        return $this->systemTimeInHours() * $this->user->nurseInfo->hourly_rate;
    }

    /**
     * 1. Need to go through each patient
     * 2. Check if ccm plus is enabled for patient
     * 3. If not enabled, sum app total time per ccm_type and
     *    pay with low and high rates.
     * 4. If enabled:
     * 4.1 get total time of patient (to know which ranges have been satisfied,
     * 4.2 get total time of ccm in each range
     * 4.3 get successful call in each range
     * 4.4 pay percentage of time in range * VF.
     *
     * @throws \Exception
     *
     * @return float
     */
    private function getVariableRatePay()
    {
        $calculationResult           = $this->variablePayCalculator->calculate($this->user);
        $this->visits                = $calculationResult->visits;
        $this->bhiVisits             = $calculationResult->bhiVisits;
        $this->pcmVisits             = $calculationResult->pcmVisits;
        $this->visitsCount           = round($calculationResult->visitsCount, 2);
        $this->ccmPlusAlgoEnabled    = $calculationResult->ccmPlusAlgoEnabled;
        $this->altAlgoEnabled        = $calculationResult->altAlgoEnabled;
        $this->visitsPerDayAvailable = true;

        return round($calculationResult->totalPay, 2);
    }

    private function getVisitsForDay(Collection $coll, string $dateStr)
    {
        $visitsCountForDay = 0.0;
        $coll->each(function (Collection $patientsPerDayColl) use (&$visitsCountForDay, $dateStr) {
            $patientsPerDayColl->each(function (array $perPatient, $key) use (&$visitsCountForDay, $dateStr) {
                if ($key !== $dateStr) {
                    return;
                }

                $visitsCountForDay += $perPatient['count'];
            });
        });

        return $visitsCountForDay;
    }

    private function setViewModelVariables()
    {
        $table  = collect();
        $period = CarbonPeriod::between($this->startDate, $this->endDate);

        $totalTimeTowardsCcm = 0;
        $totalTimeAfterCcm   = 0;

        $variablePayPerDay = collect();
        if ($this->variablePay) {
            $this->variablePayForNurse->each(function (NurseCareRateLog $log) use (
                $variablePayPerDay,
                &$totalTimeTowardsCcm,
                &$totalTimeAfterCcm
            ) {
                $dateStr = $log->created_at->toDateString();
                $current = $variablePayPerDay->get($dateStr, [
                    'towards' => 0,
                    'after'   => 0,
                ]);

                if ('accrued_towards_ccm' === $log->ccm_type) {
                    $totalTimeTowardsCcm += $log->increment;
                    $current['towards'] = $current['towards'] + $log->increment;
                } elseif ('accrued_after_ccm' === $log->ccm_type) {
                    $totalTimeAfterCcm += $log->increment;
                    $current['after'] = $current['after'] + $log->increment;
                }

                $variablePayPerDay->put($dateStr, $current);
            });
        }

        foreach ($period as $date) {
            $dateStr = $date->toDateString();

            //region visits per day
            $visitsCountForDay = $this->getVisitsForDay($this->visits, $dateStr) +
                $this->getVisitsForDay($this->bhiVisits, $dateStr) +
                $this->getVisitsForDay($this->pcmVisits, $dateStr);
            //endregion

            //region time per day
            $dataForDay = $this->aggregatedTotalTime->first(
                function ($value) use ($dateStr) {
                    return 0 == $value->is_billable && $value->date == $dateStr;
                }
            );

            $minutes = $dataForDay
                ? round($dataForDay->total_time / 60, 2)
                : 0;

            $row = [
                'formatted_time' => minutesToHhMm($minutes),
            ];

            if ($this->variablePay) {
                $variablePayForDay = $variablePayPerDay->get($dateStr, [
                    'towards' => 0,
                    'after'   => 0,
                ]);

                $row['towards']      = round($variablePayForDay['towards'] / 3600, 2);
                $row['after']        = round($variablePayForDay['after'] / 3600, 2);
                $row['visits_count'] = round($visitsCountForDay, 2);
            }

            $table->put(
                $dateStr,
                $row
            );
            //endregion
        }

        $this->timePerDay                 = $table;
        $this->totalTimeTowardsCcmInHours = round($totalTimeTowardsCcm / 3600, 1);
        $this->totalTimeAfterCcmInHours   = round($totalTimeAfterCcm / 3600, 1);
    }

    /**
     * Total system time in seconds.
     *
     * @return int
     */
    private function totalSystemTimeInSeconds()
    {
        return (int) $this->aggregatedTotalTime
            ->where('is_billable', false)
            ->sum('total_time');
    }
}
