<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ViewModels;

use App\NurseInvoiceExtra;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Spatie\ViewModels\ViewModel;

class CareCoachInvoiceViewModel extends ViewModel
{
    const DATE_FORMAT = 'jS M, Y';
    /**
     * @var Collection
     */
    public $itemizedData;
    /**
     * @var string
     */
    public $note;
    /**
     * @var User
     */
    public $user;
    /**
     * @var bool
     */
    public $variablePay;
    /**
     * @var Carbon
     */
    protected $endDate;
    /**
     * @var int
     */
    protected $extraTime;
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
    protected $variablePaySummary;
    private $amountPayable;

    private $bonus;

    /**
     * @var float the total pay with fixed rate algorithm
     */
    private $fixedRatePay;

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
     * @var float the total pay with variable rate algorithm
     */
    private $variableRatePay;

    /**
     * CareCoachInvoiceViewModel constructor.
     *
     * @param User       $user
     * @param Carbon     $startDate
     * @param Carbon     $endDate
     * @param Collection $itemizedData
     * @param string     $note
     * @param bool       $variablePay
     * @param Collection $variablePaySummary
     */
    public function __construct(
        User $user,
        Carbon $startDate,
        Carbon $endDate,
        Collection $itemizedData,
        string $note,
        bool $variablePay,
        Collection $variablePaySummary
    ) {
        $this->user         = $user;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->itemizedData = $itemizedData->flatten();
        $this->note         = $note;
        $this->variablePay  = $variablePay;

        $this->totalSystemTime = (int) $this->itemizedData
            ->where('is_billable', false)
            ->sum('total_time');

        $this->variablePaySummary = $variablePaySummary->flatten();

        $this->nurseExtras = NurseInvoiceExtra::where('user_id', $this->user->id)->get();

        $this->extraTime = $this->nurseExtras
            ->where('unit', 'minutes')
            ->sum('value');

        $this->bonus = $this->nurseExtras
            ->where('unit', 'usd')
            ->sum('value');

        $this->setPayableAmount();
    }

    public function addedTime()
    {
        return ceil(($this->extraTime * 2) / 60) / 2;
    }

    public function addedTimeAmount()
    {
        return $this->addedTime() * $this->user->nurseInfo->hourly_rate;
    }

    public function bonus()
    {
        return ceil($this->bonus);
    }

    public function endDate()
    {
        return $this->endDate->format(self::DATE_FORMAT);
    }

    public function hasAddedTime()
    {
        return 0 != $this->extraTime;
    }

    public function invoiceAmount()
    {
        return "\${$this->amountPayable}";
    }

    public function invoiceTable()
    {
        $table  = collect();
        $period = CarbonPeriod::between($this->startDate, $this->endDate);

        foreach ($period as $date) {
            $dateStr = $date->toDateString();

            $dataForDay = $this->itemizedData->first(
                function ($value) use ($dateStr) {
                    return 0 == $value->is_billable && $value->date == $dateStr;
                }
            );

            $hours = $dataForDay
                ? round($dataForDay->total_time / 3600, 1)
                : 0;

            $minutes = $dataForDay
                ? round($dataForDay->total_time / 60, 2)
                : 0;

            $row = [
                'hours'   => $hours,
                'minutes' => $minutes,
            ];

            if ($this->variablePay) {
                $towards = $this->variablePaySummary->first(
                    function ($careLog) use ($dateStr) {
                        return 'accrued_towards_ccm' == $careLog->ccm_type && $careLog->date == $dateStr;
                    }
                );

                $row['towards'] = $towards
                    ? round($towards->total_time / 3600, 1)
                    : 0;

                $after = $this->variablePaySummary->first(
                    function ($careLog) use ($dateStr) {
                        return 'accrued_after_ccm' == $careLog->ccm_type && $careLog->date == $dateStr;
                    }
                );

                $row['after'] = $after
                    ? round($after->total_time / 3600, 1)
                    : 0;
            }

            $table->put(
                $dateStr,
                $row
            );
        }

        return $table;
    }

    public function startDate()
    {
        return $this->startDate->format(self::DATE_FORMAT);
    }

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

    public function systemTimeInMinutes()
    {
        return $this->systemTimeInHours() * 60;
    }

    public function totalBillableRate()
    {
        $fixedRateMessage = "\${$this->fixedRatePay} (Fixed Rate: \${$this->user->nurseInfo->hourly_rate}/hr).";

        $high_rate = $this->user->nurseInfo->high_rate;
        $low_rate  = $this->user->nurseInfo->low_rate;

        $variableRateMessage = "\${$this->variableRatePay} (Variable Rates: \$$high_rate/hr or \$$low_rate/hr).";

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

    public function totalTimeAfterCcm()
    {
        if (is_null($this->totalTimeAfterCcm)) {
            $this->totalTimeAfterCcm = round(
                $this->variablePaySummary
                    ->where('ccm_type', 'accrued_after_ccm')
                    ->sum('total_time') / 3600,
                1
            );
        }

        return $this->totalTimeAfterCcm;
    }

    public function totalTimeTowardsCcm()
    {
        if (is_null($this->totalTimeTowardsCcm)) {
            $this->totalTimeTowardsCcm = round(
                $this->variablePaySummary
                    ->where('ccm_type', 'accrued_towards_ccm')
                    ->sum('total_time') / 3600,
                1
            );
        }

        return $this->totalTimeTowardsCcm;
    }

    private function setPayableAmount()
    {
        $this->fixedRatePay = $this->systemTimeInHours() * $this->user->nurseInfo->hourly_rate;
        if ( ! $this->variablePay) {
            $this->amountPayable = $this->fixedRatePay;
        } else {
            $this->variableRatePay = $this->totalTimeAfterCcm() * $this->user->nurseInfo->low_rate
                + $this->totalTimeTowardsCcm() * $this->user->nurseInfo->high_rate;
            if ($this->fixedRatePay > $this->variableRatePay) {
                $this->amountPayable = $this->fixedRatePay;
                $this->variablePay   = false;
            } else {
                $this->amountPayable = $this->variableRatePay;
            }
        }

        //Add extratime
        $this->amountPayable += (ceil($this->extraTime / 60) * $this->user->nurseInfo->hourly_rate) + $this->bonus;
    }
}
