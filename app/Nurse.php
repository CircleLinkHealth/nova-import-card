<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Nurse extends Model
{
    //nurse mapping for import csv
    public static $nurseMap = [
        'Patricia' => 1920,
        'Katie'    => 2159,
        'Lydia'    => 1755,
        'Sue'      => 1877,
        'Monique'  => 2332,
        'Erin'     => 2398,
        'Kerri'    => 2012,
    ];

    protected $table = 'nurse_info';

    protected $fillable = [
        'user_id',
        'status',
        'license',
        'hourly_rate',
        'spanish',
        'isNLC',
    ];

    public function scopeActive()
    {

        return User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center');
        })->where('user_status', 1)->get();

    }

    public static function activeNursesForUI()
    {

        return User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center');
        })->where('user_status', 1)->pluck('display_name', 'id');


    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function summary()
    {
        return $this->hasMany(NurseMonthlySummary::class);
    }

    /**
     * Upcoming (future) contact windows.
     *
     * @return mixed
     */
    public function upcomingWindows()
    {
        return $this->hasMany(NurseContactWindow::class, 'nurse_info_id', 'id')->upcoming();
    }       

    /**
     * Contact Windows (Schedule).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function windows()
    {
        return $this->hasMany(NurseContactWindow::class, 'nurse_info_id', 'id');
    }
            
    public function calls()
    {

        return $this->hasMany('App\Call');
    }

    public function states()
    {

        return $this->belongsToMany(State::class, 'nurse_info_state');
    }
    
    public function callStatsForRange(Carbon $start, Carbon $end){

                            

    }

    public function createOrIncrementNurseWindow( // note, not storing call data for now.
        Nurse $nurse,
        $toAddToAccuredTowardsCCM, $toAddToAccuredAfterCCM
    ) {

        $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
        $report = PatientMonthlySummary::where('patient_info_id', $this->id)->where('month_year', $day_start)->exists();

        if($report){

            $report->accured_after_ccm = $toAddToAccuredAfterCCM + $report->accured_after_ccm;
            $report->accured_towards_ccm = $toAddToAccuredTowardsCCM + $report->accured_towards_ccm;
            $report->save();

            return $report;

         } else {

            return NurseMonthlySummary::create([

                'nurse_id' => $nurse->id,
                'month_year' => $day_start,
                'accrued_after_ccm' => $toAddToAccuredAfterCCM,
                'accrued_towards_ccm' => $toAddToAccuredTowardsCCM,
                'no_of_calls' => 0,
                'no_of_successful_calls' => 0


            ]);

        }

    }

    public function adjustCCMPaybleForActivity(Activity $activity){

        $toAddToAccuredTowardsCCM = 0;
        $toAddToAccuredAfterCCM = 0;

        $patient = $activity->patient->patientInfo;
        $ccm_after_activity = $patient->cur_month_activity_time;
        $isComplex = $patient->isCCMComplex();

        $ccm_before_activity = $ccm_after_activity - $activity->duration;

        if ($isComplex) {


        } else {

            //if patient was already over 20 mins.
            if ($ccm_before_activity >= 1200) {

                //add all time to post, paid at lower rate
                $toAddToAccuredAfterCCM = $activity->duration;

            } elseif ($ccm_before_activity < 1200) { //if patient hasn't met 20mins

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
            'toAddToAccuredAfterCCM' => $toAddToAccuredAfterCCM
        ];

    }

}
