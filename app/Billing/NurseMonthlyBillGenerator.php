<?php

namespace App\Billing;

use App\NurseInfo;
use App\PageTimer;
use Carbon\Carbon;

class NurseMonthlyBillGenerator
{

    protected $nurse;
    protected $startDate;
    protected $endDate;

    //Billing Results
    protected $payable;
    protected $systemTime;

    public function __construct(NurseInfo $newNurse, Carbon $billingDateStart, Carbon $billingDateEnd){

        $this->nurse = $newNurse;
        $this->startDate = $billingDateStart;
        $this->endDate = $billingDateEnd;

    }

    public function handle(){



    }

    public function getCCMTimeForNurseForPeriod(){


        $systemTime = PageTimer::where('provider_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('updated_at', '>=' , $this->startDate->toDateString())
                    ->where('updated_at', '<=' , $this->endDate->toDateString());
            })
//				->whereNotNull('activity_type')
            ->sum('duration');

        $this->payable = ($systemTime / 3600) * $this->nurse->hourly_rate;

        return $this->payable;

    }

}