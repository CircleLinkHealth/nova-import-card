<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class NurseMonthlySummary extends Model
{

    protected $fillable = [
        'nurse_id',
        'month_year',
        'accrued_after_ccm',
        'accrued_towards_ccm',
        'no_of_calls',
        'no_of_successful_calls'
    ];

    public function nurse()
    {

        $this->belongsTo(Nurse::class, 'id', 'nurse_id');

    }

    public function createOrIncrementNurseSummary( // note, not storing call data for now.
        $nurse, $toAddToAccruedTowardsCCM, $toAddToAccruedAfterCCM, $activityId
    ) {

        $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
        $report = self::where('nurse_id', $nurse->id)->where('month_year', $day_start)->first();

        if($report){

            $report->accrued_after_ccm = $toAddToAccruedAfterCCM + $report->accrued_after_ccm;
            $report->accrued_towards_ccm = $toAddToAccruedTowardsCCM + $report->accrued_towards_ccm;
            $report->save();

        } else {

            $report = self::create([

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

        $over_20 = $ccm_before_activity >= 1200;
        $under_20 = $ccm_before_activity < 1200;

        if ($isComplex) {


        } else {

            //if patient was already over 20 mins.
            if ($over_20) {

                //add all time to post, paid at lower rate
                $toAddToAccuredAfterCCM = $activity->duration;

            } elseif ($under_20) { //if patient hasn't met 20mins

                if ($ccm_after_activity > 1200) {//patient reached 20mins with this activity

                    $toAddToAccuredAfterCCM = abs(1200 - $ccm_after_activity);
                    $toAddToAccuredTowardsCCM = abs(1200 - $ccm_before_activity);

                } else {//patient is still under 20mins

                    //all to pre_ccm
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
