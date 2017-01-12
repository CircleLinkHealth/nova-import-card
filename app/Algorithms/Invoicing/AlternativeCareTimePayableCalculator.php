<?php namespace App\Algorithms\Invoicing;

use App\Activity;
use App\NurseCareRateLog;
use App\NurseMonthlySummary;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 1/12/17
 * Time: 1:25 PM
 */
class AlternativeCareTimePayableCalculator
{

    public function createOrIncrementNurseSummary( // note, not storing call data for now.
        $nurse, $toAddToAccruedTowardsCCM, $toAddToAccruedAfterCCM, $activityId
    ) {

        $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
        $report = NurseMonthlySummary::where('nurse_id', $nurse->id)->where('month_year', $day_start)->first();

        if($report){

            $report->accrued_after_ccm = $toAddToAccruedAfterCCM + $report->accrued_after_ccm;
            $report->accrued_towards_ccm = $toAddToAccruedTowardsCCM + $report->accrued_towards_ccm;
            $report->save();

        } else {

            $report = NurseMonthlySummary::create([

                'nurse_id' => $nurse->id,
                'month_year' => $day_start,
                'accrued_after_ccm' => $toAddToAccruedAfterCCM,
                'accrued_towards_ccm' => $toAddToAccruedTowardsCCM,
                'no_of_calls' => 0,
                'no_of_successful_calls' => 0
            ]);

        }

        if($toAddToAccruedAfterCCM != 0){

            NurseCareRateLog::create([

                'nurse_id' => $nurse->id,
                'activity_id' => $activityId,
                'ccm_type' => 'accrued_after_ccm',
                'increment' => $toAddToAccruedAfterCCM

            ]);
        }

        if($toAddToAccruedTowardsCCM != 0){

            NurseCareRateLog::create([

                'nurse_id' => $nurse->id,
                'activity_id' => $activityId,
                'ccm_type' => 'accrued_towards_ccm',
                'increment' => $toAddToAccruedTowardsCCM

            ]);
        }

        return $report;

    }

    public function adjustCCMPaybleForActivity(Activity $activity){

        $toAddToAccuredTowardsCCM = 0;
        $toAddToAccuredAfterCCM = 0;
        $patient = $activity->patient->patientInfo;

        $ccm_after_activity = intval($patient->cur_month_activity_time);
        $isComplex = $patient->isCCMComplex();

        $ccm_before_activity = $ccm_after_activity - $activity->duration;

        //logic switches:

        //read as: if ccm before was over 20...
        //20mins
        $ccm_before_over_20 = $ccm_before_activity >= 1200;
        $ccm_before_under_20 = $ccm_before_activity < 1200;
        $ccm_after_over_20 = $ccm_after_activity >= 1200;
        $ccm_after_under_20 = $ccm_after_activity < 1200;

        //60mins
        $ccm_before_under_60 = $ccm_before_activity < 3600;
        $ccm_before_over_60 = $ccm_before_activity >= 3600;
        $ccm_after_under_60 = $ccm_after_activity < 3600;
        $ccm_after_over_60 = $ccm_after_activity >= 3600;

        //90mins
        $ccm_before_under_90 = $ccm_before_activity < 5400;
        $ccm_before_over_90 = $ccm_before_activity >= 5400;
        $ccm_after_under_90 = $ccm_after_activity < 5400;
        $ccm_after_over_90 = $ccm_after_activity >= 5400;



        if ($isComplex) {

            if($ccm_before_over_90){

                // before: 6000, add: 200, total: 6200; target: 5400
                // towards: 0, after: 200

                $toAddToAccuredAfterCCM = $activity->duration;

            } elseif($ccm_before_under_90){

                if($ccm_after_over_90){//patient just reached 90

                    // before: 3000, add: 1000, total: 4000; target: 5400  H:0  L:1000
                    // towards: 0, after: 1000

                    // before: 4000, add: 2000, total: 6000; target: 5400  H:  L:
                    // towards: 1400, after: 600

                    $toAddToAccuredAfterCCM = $ccm_after_activity - 5400;
                    $toAddToAccuredTowardsCCM = 5400 - $ccm_before_activity;

                } else { //still under

                    /*
                     * So when a patient crosses from 45 - 64 minutes (for e.g., or other non-billable to billable states):
                     * 1) I have to backtrack all nurses that cared for this patient
                     * 2) figure out how much time each of them gave the patient
                     * 3) based on the time allowted, convert low rate minutes to high rate minutes for each nurse
                     */



                }


            }


        } else {

            if ($ccm_before_over_20) { //if patient was already over 20 mins.

                // before: 1200, add: 200, total: 1400; target: 1200
                // towards: 0, after: 200

                $toAddToAccuredAfterCCM = $activity->duration;

            } elseif ($ccm_before_under_20) { //if patient hasn't met 20mins

                if ($ccm_after_over_20) { //patient reached 20 mins with this activity

                    // before: 600, add: 720, total: 1320; target: 1200
                    // towards: 600, after: 120

                    $toAddToAccuredAfterCCM = $ccm_after_activity - 1200;
                    $toAddToAccuredTowardsCCM = 1200 - $ccm_before_activity;

                } else {//patient is still under 20mins

                    // before: 200, add: 200, total: 400; target: 1200
                    // towards: 200, after: 0

                    $toAddToAccuredTowardsCCM = $activity->duration;

                }

            }

        }

        return [
            'toAddToAccuredTowardsCCM' => $toAddToAccuredTowardsCCM,
            'toAddToAccuredAfterCCM' => $toAddToAccuredAfterCCM,
            'activity_id' => $activity->id
        ];

    }

}