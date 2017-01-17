<?php namespace App\Billing\NurseInvoices;


use App\Activity;
use App\Nurse;
use App\NurseCareRateLog;
use App\NurseMonthlySummary;
use Carbon\Carbon;

class VariablePay extends NurseInvoice
{

    const OVER_PER_MINUTE = 10 / 60;
    const UNDER_PER_MINUTE = 30 / 60;
    private $ccm_over_duration;
    private $ccm_over_payable;
    private $ccm_under_duration;
    private $ccm_under_payable;
    private $report;

    public function __construct(
        Nurse $nurse,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($nurse, $start, $end);

        $this->start = $start;
        $this->end = $end;

        $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
        $this->report = NurseMonthlySummary::where('nurse_id', $nurse->id)->where('month_year', $day_start)->first();

        if($this->report != null){

            $this->ccm_over_duration = round($this->report->accrued_after_ccm / 3600, 1);
            $this->ccm_under_duration = round($this->report->accrued_towards_ccm /3600 , 1);

            $this->ccm_under_payable = ($this->report->accrued_towards_ccm / 3600) * 10;
            $this->ccm_over_payable = ($this->report->accrued_after_ccm / 3600) * 30;

            $this->data['after'] = round(($this->ccm_over_duration / 3600) , 1);
            $this->data['towards'] = round(($this->ccm_under_duration / 3600) , 1);

            $this->data['payable'] = round($this->ccm_over_payable + $this->ccm_over_payable, 2);

        } else {

            $this->ccm_over_duration = 0;
            $this->ccm_under_duration = 0;
            $this->ccm_under_payable = 0;
            $this->ccm_over_payable = 0;
            $this->data['after'] = 0;
            $this->data['towards'] = 0;
            $this->data['payable'] = 0;

        }


    }

    public function activityDurationForPeriod()
    {

        return [

            'total'     => $this->ccm_under_payable + $this->ccm_over_payable,
            'High Rate' => $this->ccm_under_payable,
            'Low Rate'  => $this->ccm_over_payable,

        ];

    }

    public function getItemizedActivities()
    {

        $dayCounter = $this->start->toDateString();

        $this->data['total']['towards'] = $this->ccm_under_duration;
        $this->data['total']['after'] = $this->ccm_over_duration;

        while ($this->end->toDateString() >= $dayCounter) {

            $raw_after = NurseCareRateLog::where('nurse_id', $this->nurse->id)
                ->where(function ($q) use
                (
                    $dayCounter
                ) {
                    $q->where('created_at', '>=', Carbon::parse($dayCounter)->startOfDay())
                        ->where('created_at', '<=', Carbon::parse($dayCounter)->endOfDay());
                })
                ->where('ccm_type', 'accrued_after_ccm')
                ->sum('increment');

            $this->data[$dayCounter]['after'] = round($raw_after / 3600, 1);

            $raw_towards = NurseCareRateLog::where('nurse_id', $this->nurse->id)
                ->where(function ($q) use
                (
                    $dayCounter
                ) {
                    $q->where('created_at', '>=', Carbon::parse($dayCounter)->startOfDay())
                        ->where('created_at', '<=', Carbon::parse($dayCounter)->endOfDay());
                })
                ->where('ccm_type', 'accrued_towards_ccm')
                ->sum('increment');

            $this->data[$dayCounter]['towards'] = round($raw_towards / 3600, 1);

            $total = NurseCareRateLog::where('nurse_id', $this->nurse->id)
                ->where(function ($q) use
                (
                    $dayCounter
                ) {
                    $q->where('created_at', '>=', Carbon::parse($dayCounter)->startOfDay())
                        ->where('created_at', '<=', Carbon::parse($dayCounter)->endOfDay());
                })
                ->sum('increment');

            $this->data[$dayCounter]['total'] = round($total / 3600, 1);

            $dayCounter = Carbon::parse($dayCounter)->addDay(1)->toDateString();

        }

        return $this->data;

    }

}