<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\ViewModels;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Algorithms\VariablePayCalculator;
use CircleLinkHealth\Nurseinvoices\ValueObjects\VisitFeePay;
use Illuminate\Support\Collection;
use Spatie\ViewModels\ViewModel;

class Invoice extends ViewModel
{
    const DATE_FORMAT = 'jS M, Y';

    public Collection $aggregatedTotalTime;

    public float $baseSalary;

    public bool $changedToFixedRateBecauseItYieldedMore = false;

    public float $fixedRatePay = 0.0;

    public string $nurseFullName = '';

    public float $nurseHighRate = 0.0;

    public float $nurseHourlyRate = 0.0;

    public float $nurseLowRate = 0.0;

    public float $nurseVisitFee = 0.0;

    public ?Collection $timePerDay = null;

    public int $totalSystemTime = 0;

    public float $totalTimeAfterCcmInHours = 0.0;

    public float $totalTimeTowardsCcmInHours = 0.0;

    public bool $variablePay = false;

    public float $variableRatePay = 0.0;

    /**
     * @var ?Collection A matrix array, [patient id => [ cs code => [ range => pay ] ] ]
     */
    public ?Collection $visits = null;

    public float $visitsCount = 0.0;

    public bool $visitsPerDayAvailable = false;

    protected ?Carbon $endDate = null;

    protected int $extraTime = 0;

    /**
     * Do not make this methods available to classes consuming this ViewModel.
     *
     * @var array array
     */
    protected $ignore = ['user'];

    protected ?Carbon $startDate = null;

    protected ?Collection $variablePayForNurse;

    private ?float $bonus;

    /**
     * @var \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private $nurseExtras;

    private ?float $systemTimeInHours = null;

    private User $user;

    private VariablePayCalculator $variablePayCalculator;

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
        $this->aggregatedTotalTime   = $this->getPayableTotalTime($aggregatedTotalTime);
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
        $this->variablePayForNurse   = $this->getVariablePaySummaryForNurse();

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
     * Payable amount due to extra time.
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

        $variableRatePay     = number_format($this->variableRatePay, 2);
        $variableRateMessage = "\${$variableRatePay} (Visit-based: $this->visitsCount visits).";

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

    private function getPayableTotalTime(Collection $aggregatedTotalTime)
    {
        $flat = $aggregatedTotalTime->flatten();
        if (empty($this->user->nurseInfo->start_date)) {
            return $flat;
        }
        $nurseStartDate = $this->user->nurseInfo->start_date;

        return $flat->filter(function ($value) use ($nurseStartDate) {
            $date = Carbon::parse($value->date)->startOfDay();

            return $date->greaterThanOrEqualTo($nurseStartDate);
        });
    }

    private function getVariablePaySummaryForNurse()
    {
        $variablePayMap = $this->variablePayCalculator->getForNurses();

        return $variablePayMap->filter(function ($f) {
            return $f->nurse_id === $this->user->nurseInfo->id;
        });
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
        $this->visits                = $calculationResult->visitsPerPatientPerChargeableServiceCode;
        $this->visitsCount           = round($calculationResult->visitsCount, 2);
        $this->visitsPerDayAvailable = true;

        return round($calculationResult->totalPay, 2);
    }

    private function getVisitsForDay(string $targetDate, Collection $visitsPerPatientPerCsCode = null)
    {
        $visitsCountForDay = 0.0;
        if ( ! $visitsPerPatientPerCsCode) {
            return $visitsCountForDay;
        }
        $visitsPerPatientPerCsCode->each(function (Collection $patientsPerCsCode) use (&$visitsCountForDay, $targetDate) {
            $patientsPerCsCode->each(function (Collection $patientsPerDay) use (&$visitsCountForDay, $targetDate) {
                $patientsPerDay->each(function (VisitFeePay $perPatient, $dateKey) use (&$visitsCountForDay, $targetDate) {
                    if ($dateKey !== $targetDate) {
                        return;
                    }

                    $visitsCountForDay += $perPatient->count;
                });
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
            $visitsCountForDay = $this->getVisitsForDay($dateStr, $this->visits);
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