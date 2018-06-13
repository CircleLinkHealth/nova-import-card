<?php namespace App\Algorithms\Invoicing;

use App\Activity;
use App\Nurse;
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

    protected $nurse;
    protected $nurseReport;
    protected $month;

    public function __construct(Nurse $nurse)
    {

        $this->nurse = $nurse;

        $this->month = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
        $report = NurseMonthlySummary::where('nurse_id', $this->nurse->id)->where('month_year', $this->month)->first();

        $this->nurseReport = $report;
    }

    public function createOrIncrementNurseSummary( // note, not storing call data for now.
        $toAddToAccruedTowardsCCM,
        $toAddToAccruedAfterCCM,
        $activityId
    ) {

        if ($this->nurseReport) {
            $this->nurseReport->accrued_after_ccm = $toAddToAccruedAfterCCM + $this->nurseReport->accrued_after_ccm;
            $this->nurseReport->accrued_towards_ccm = $toAddToAccruedTowardsCCM + $this->nurseReport->accrued_towards_ccm;
        } else {
            $this->nurseReport = NurseMonthlySummary::create([

                'nurse_id'               => $this->nurse->id,
                'month_year'             => $this->month,
                'accrued_after_ccm'      => $toAddToAccruedAfterCCM,
                'accrued_towards_ccm'    => $toAddToAccruedTowardsCCM,
                'no_of_calls'            => 0,
                'no_of_successful_calls' => 0,
            ]);
        }

        if ($toAddToAccruedAfterCCM != 0) {
            NurseCareRateLog::create([

                'nurse_id'    => $this->nurse->id,
                'activity_id' => $activityId,
                'ccm_type'    => 'accrued_after_ccm',
                'increment'   => $toAddToAccruedAfterCCM,

            ]);
        }

        if ($toAddToAccruedTowardsCCM != 0) {
            NurseCareRateLog::create([

                'nurse_id'    => $this->nurse->id,
                'activity_id' => $activityId,
                'ccm_type'    => 'accrued_towards_ccm',
                'increment'   => $toAddToAccruedTowardsCCM,

            ]);
        }

        $this->nurseReport->save();

        return $this->nurseReport;
    }

    public function adjustNursePayForActivity(Activity $activity)
    {

        $toAddToAccuredTowardsCCM = 0;
        $toAddToAccuredAfterCCM   = 0;
        $user                     = $activity->patient;
        $monthYear                = Carbon::parse($activity->performed_at)->firstOfMonth()->toDateString();

        $summary = $user->patientSummaries()
                        ->whereMonthYear($monthYear)
                        ->first();

        $patient = $user->patientInfo;

        $totalTime = $activity->is_behavioral
            ? $summary->bhi_time
            : $summary->ccm_time;

        $ccm_after_activity = intval($totalTime);
        $isComplex = $user->isCCMComplex();

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

        //120mins
        $ccm_before_under_120 = $ccm_before_activity < 7200;
        $ccm_before_over_120 = $ccm_before_activity >= 7200;
        $ccm_after_under_120 = $ccm_after_activity < 7200;
        $ccm_after_over_120 = $ccm_after_activity >= 7200;

        debug(['before' => $ccm_before_activity, 'after' => $ccm_after_activity]);

        if ($isComplex) {
//            dd($ccm_after_activity, $ccm_before_activity);

            if ($ccm_before_over_120) {
                // before: 8000, add: 200, total: 8200; target (was): 7200
                // towards: 0, after: 200

                $toAddToAccuredAfterCCM = $activity->duration;
            } elseif ($ccm_before_under_120 && $ccm_before_over_90) {
                if ($ccm_after_over_120) {//patient just reached 120

                    // before: 0, add: 20, total: 20; target: 20
                    //  Hi: x + 20       Li: y + 0

                    // current: 30, add: 50, total: 80; target: 60
                    //  Hf: (CURRENT TOTAL + 20) + 40 = +40  Lf: CURRENT TOTAL - (30 - 20) + (80 - 60) = +10
                    //  Hf: (CURRENT TOTAL + 20) + 40 = +40  Lf: CURRENT TOTAL - (old_ccm - previous_goal) + (new_ccm - current_goal)

                    $toAddToAccuredTowardsCCM += 1800;
                    $toAddToAccuredAfterCCM = ($ccm_after_activity - 7200) + (5400 - $ccm_before_activity);


//                    $patient = User::find($activity->patient_id)->patientInfo;
//                    $nurse = User::find($activity->logger_id)->nurseInfo;
//
//                    $nurseCareForPatient = $nurse->careGivenToPatientForCurrentMonth($patient, $nurse);
                } else { //still under, mins go to

                    $toAddToAccuredAfterCCM = $activity->duration;
                }
            } elseif ($ccm_before_under_90 && $ccm_before_over_60) {
                if ($ccm_after_over_90) {//patient just reached 90

                    // before: 0, add: 20, total: 20; target: 20
                    //  Hi: x + 20       Li: y + 0

                    // current: 30, add: 50, total: 80; target: 60
                    //  Hf: (CURRENT TOTAL + 20) + 40 = +40  Lf: CURRENT TOTAL - (30 - 20) + (80 - 60) = +10
                    //  Hf: (CURRENT TOTAL + 20) + 40 = +40  Lf: CURRENT TOTAL - (old_ccm - previous_goal) + (new_ccm - current_goal)

                    $toAddToAccuredTowardsCCM += 1800;
                    $toAddToAccuredAfterCCM = ($ccm_after_activity - 5400) + (3600 - $ccm_before_activity);


//                    $patient = User::find($activity->patient_id)->patientInfo;
//                    $nurse = User::find($activity->logger_id)->nurseInfo;
//
//                    $nurseCareForPatient = $nurse->careGivenToPatientForCurrentMonth($patient, $nurse);
                } else { //still under, mins go to

                    $toAddToAccuredAfterCCM = $activity->duration;
                }
            } elseif ($ccm_before_under_60 && $ccm_before_over_20) { //if patient was already over 20 mins.

                if ($ccm_after_over_60) {//patient just reached 60

                    $toAddToAccuredTowardsCCM += 2400; //40 mins

                    //Removes all the credit given for lower rate (20 - mins over 20) + new mins over 60
                    $toAddToAccuredAfterCCM = ($ccm_after_activity - 3600) + (1200 - $ccm_before_activity);
                } else { //still under, mins go to

                    $toAddToAccuredAfterCCM = $activity->duration;
                }
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
        } else { //NOT COMPLEX

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

        $this->createOrIncrementNurseSummary($toAddToAccuredTowardsCCM, $toAddToAccuredAfterCCM, $activity->id);

        return [
            'toAddToAccuredTowardsCCM' => $toAddToAccuredTowardsCCM,
            'toAddToAccuredAfterCCM'   => $toAddToAccuredAfterCCM,
            'activity_id'              => $activity->id,
        ];
    }

    public function adjustPayOnCCMComplexSwitch60Mins()
    {

        /*
         * If patient was at 64 and was turned to complex:
         *  RN has HR at 20 and LR at 44
         *  RN should now have HR + 40 and LR at (64 - 60) + (20 - 44) = 4 + -24 = -20.
         *
         * ($ccm_after_activity - 3600) + (1200 - $ccm_before_activity);
         */

        $toAddToAccruedTowardsCCM = 0;
        $toAddToAccruedAfterCCM = 0;

        $toAddToAccruedTowardsCCM += 2400;
        $toAddToAccruedAfterCCM -= 2400;

        $this->createOrIncrementNurseSummary(
            $toAddToAccruedTowardsCCM,
            $toAddToAccruedAfterCCM,
            null
        );

        return $this->nurseReport;
    }
}
