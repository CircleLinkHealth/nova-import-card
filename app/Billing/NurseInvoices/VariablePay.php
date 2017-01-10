<?php namespace App\Billing\NurseInvoices;


use App\Activity;
use App\Nurse;
use Carbon\Carbon;

class VariablePay extends NurseInvoice
{

    private $ccm_over_duration;
    private $ccm_under_duration;

    const OVER_PER_HOUR = 30;
    const UNDER_PER_HOUR = 60;

    public function __construct(
        Nurse $nurse,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($nurse, $start, $end);
    }

    public function activityDurationForPeriod(){

        $this->ccm_over_duration = Activity::where('logger_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('updated_at', '>=', $this->start)
                    ->where('updated_at', '<=', $this->end);
            })
            ->sum('post_ccm_duration');

        $this->ccm_under_duration = Activity::where('logger_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('updated_at', '>=', $this->start)
                    ->where('updated_at', '<=', $this->end);
            })
            ->sum('pre_ccm_duration');

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

            $this->data[Carbon::parse($activity[0]['created_at'])->toDateString()]['ccm_over'] = $activity->sum('post_ccm_duration');
            $this->data[Carbon::parse($activity[0]['created_at'])->toDateString()]['ccm_under'] = $activity->sum('pre_ccm_duration');

        };

        return $this->data;

    }

    public function calculatePeriodPayable(){





    }

}