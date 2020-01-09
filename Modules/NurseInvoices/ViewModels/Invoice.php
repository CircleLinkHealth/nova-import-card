<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\ViewModels;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
     * @var float
     */
    public $baseSalary;
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
     * @var bool
     */
    public $variablePay;
    /**
     * @var float the total pay with variable rate algorithm
     */
    public $variableRatePay = 0.0;
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
     * @var int|null
     */
    private $totalTimeAfterCcm;
    /**
     * @var int|null
     */
    private $totalTimeTowardsCcm;
    /**
     * @var User
     */
    private $user;

    /** @var VariablePayCalculator $variablePayCalculator */
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

        $variablePayMap     = $this->variablePayCalculator->getForNurses();
        $variablePaySummary = $variablePayMap->filter(function ($f) use ($user) {
            return $f->nurse_id === $user->nurseInfo->id;
        });

        $this->variablePayForNurse = $variablePaySummary;

        $this->determineBaseSalary();
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
        $fixedRateMessage = "\${$this->fixedRatePay} (Fixed Rate: \${$this->user->nurseInfo->hourly_rate}/hr).";

        if ( ! $this->variablePay && ! $this->changedToFixedRateBecauseItYieldedMore) {
            return $fixedRateMessage;
        }
        $high_rate = $this->user->nurseInfo->high_rate;
        $low_rate  = $this->user->nurseInfo->low_rate;

        $variableRateMessage = "\${$this->variableRatePay} (Variable Rate: \$$high_rate/hr or \$$low_rate/hr).";

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
        return '$'.round($this->invoiceTotalAmount(), 2);
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
     * An array showing the total time per day.
     *
     * @return Collection
     */
    public function timePerDay()
    {
        $table  = collect();
        $period = CarbonPeriod::between($this->startDate, $this->endDate);

        foreach ($period as $date) {
            $dateStr = $date->toDateString();

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
                $towards = $this->variablePayForNurse->sum(
                    function ($careLog) use ($dateStr) {
                        if ('accrued_towards_ccm' == $careLog->ccm_type && $careLog->created_at->startOfMonth()->toDateString() === $dateStr) {
                            return $careLog->increment;
                        }

                        return 0;
                    }
                );

                $row['towards'] = $towards
                    ? $towards / 3600
                    : 0;

                $after = $this->variablePayForNurse->sum(
                    function ($careLog) use ($dateStr) {
                        if ('accrued_after_ccm' == $careLog->ccm_type && $careLog->created_at->startOfMonth()->toDateString() === $dateStr) {
                            return $careLog->increment;
                        }

                        return 0;
                    }
                );

                $row['after'] = $after
                    ? $after / 3600
                    : 0;
            }

            $table->put(
                $dateStr,
                $row
            );
        }

        return $table;
    }

    /**
     * @return float|int|null
     */
    public function totalTimeAfterCcmInHours()
    {
        if (is_null($this->totalTimeAfterCcm)) {
            $this->totalTimeAfterCcm = round(
                $this->variablePayForNurse
                    ->where('ccm_type', 'accrued_after_ccm')
                    ->sum('total_time') / 3600,
                1
            );
        }

        return $this->totalTimeAfterCcm;
    }

    /**
     * @return float|int|null
     */
    public function totalTimeTowardsCcmInHours()
    {
        if (is_null($this->totalTimeTowardsCcm)) {
            $this->totalTimeTowardsCcm = round(
                $this->variablePayForNurse
                    ->where('ccm_type', 'accrued_towards_ccm')
                    ->sum('total_time') / 3600,
                1
            );
        }

        return $this->totalTimeTowardsCcm;
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
     * @return float|int
     */
    private function getVariableRatePay()
    {
        return $this->variablePayCalculator->calculate($this->user);
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
