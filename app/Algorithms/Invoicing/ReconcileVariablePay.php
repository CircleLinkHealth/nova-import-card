<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Invoicing;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\TimeTracking\Entities\Activity;

class ReconcileVariablePay
{
    private $data;
    private $end;
    private $nurses;
    private $start;

    public function __construct()
    {
        $this->nurses = [
            Nurse::$nurseMap['Patricia'],
            Nurse::$nurseMap['Sue'],
        ];

        $this->start = Carbon::parse('2017-02-01 00:00:00');
        $this->end   = Carbon::parse('2017-02-02 23:59:59');
    }

    public function adjust()
    {
        $data = [];

        foreach ($this->nurses as $nurse) {
            $vities = Activity
                ::select(\DB::raw('patient_id, sum(duration) as total'))
                    ->where('provider_id', $nurse)
                    ->where('created_at', '>=', $this->start)
                    ->where('created_at', '<=', $this->end)
                    ->groupBy('patient_id')
                    ->get();

            $data[$nurse]['HR'] = 0;
            $data[$nurse]['LR'] = 0;

            foreach ($vities as $vity) {
                if ($vity->total < 1200) {
                    $hr = $vity->total;
                    $lr = 0;
                } else {
                    $hr = 1200;
                    $lr = $vity->total - 1200;
                }

                $data[$nurse]['HR'] += $hr;
                $data[$nurse]['LR'] += $lr;
            }
        }

        return $data;
//        $reportPat = NurseMonthlySummary::where('nurse_id', 1)->where('month_year', '2017-02-01')->first();

//        $reportPat->accrued_after_ccm =

//        $reportSue = NurseMonthlySummary::where('nurse_id', 4)->where('month_year', '2017-02-01')->first();
//        $sueData['LR'] = 0;
//        $sueData['LR'] = 0;
//
    }
}
