<?php namespace App\Billing\NurseInvoices;


use App\Activity;
use App\Nurse;
use App\NurseMonthlySummary;
use Carbon\Carbon;

class VariablePay extends NurseInvoice
{

    private $ccm_over_duration;
    private $ccm_over_payable;

    private $ccm_under_duration;
    private $ccm_under_payable;

    private $report;

    const OVER_PER_MINUTE = 10/60;
    const UNDER_PER_MINUTE = 30/60;

    public function __construct(
        Nurse $nurse,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($nurse, $start, $end);

        $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
        $this->report = NurseMonthlySummary::where('nurse_id', $nurse->id)->where('month_year', $day_start)->first();

        $this->ccm_over_duration = $this->report->accrued_after_ccm;
        $this->ccm_under_duration = $this->report->accrued_towards_ccm;

        $this->ccm_under_payable = ( $this->ccm_over_duration / 3600 ) * 10;
        $this->ccm_over_payable = ( $this->ccm_under_duration / 3600 ) * 30;


    }

    public function activityDurationForPeriod(){

        return [

            'total' => $this->ccm_under_payable + $this->ccm_over_payable,
            'High Rate' => $this->ccm_under_payable,
            'Low Rate' => $this->ccm_over_payable

        ];

    }

    public function getItemizedActivities(){

        $activities = Activity::where('logger_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('updated_at', '>=', $this->start)
                    ->where('updated_at', '<=', $this->end);
            })
            ->get();


        $activities = $activities->groupBy(function($q) {
            return Carbon::parse($q->created_at)->format('d'); // grouping by days
        });

        foreach ($activities as $activity){

            $this->data[Carbon::parse($activity[0]['created_at'])->toDateString()]['duration'] = $activity->sum('duration');

        };

        return $this->data;

    }

    public function calculatePeriodPayable(){





    }

}