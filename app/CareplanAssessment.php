<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\CareplanAssessment.
 *
 * @property int                                                   $id
 * @property int                                                   $careplan_id
 * @property int                                                   $provider_approver_id
 * @property string                                                $alcohol_misuse_counseling
 * @property string                                                $diabetes_screening_interval
 * @property \Carbon\Carbon                                        $diabetes_screening_last_date
 * @property \Carbon\Carbon                                        $diabetes_screening_next_date
 * @property array|string|null                                     $diabetes_screening_risk
 * @property string                                                $eye_screening_last_date
 * @property string                                                $eye_screening_next_date
 * @property string                                                $key_treatment
 * @property array|string|null                                     $patient_functional_assistance_areas
 * @property array|string|null                                     $patient_psychosocial_areas_to_watch
 * @property string                                                $risk
 * @property array|string|null                                     $risk_factors
 * @property string                                                $tobacco_misuse_counseling
 * @property \Carbon\Carbon                                        $created_at
 * @property \Carbon\Carbon                                        $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User|null         $approver
 * @property \CircleLinkHealth\Customer\Entities\User|null         $patient
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlan|null $carePlan
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment query()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereAlcoholMisuseCounseling($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereCareplanId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereCreatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereDiabetesScreeningInterval($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereDiabetesScreeningLastDate($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereDiabetesScreeningNextDate($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereDiabetesScreeningRisk($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereEyeScreeningLastDate($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereEyeScreeningNextDate($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereKeyTreatment($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment wherePatientFunctionalAssistanceAreas($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment wherePatientPsychosocialAreasToWatch($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereProviderApproverId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereRisk($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereRiskFactors($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereTobaccoMisuseCounseling($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereUpdatedAt($value)
 * @property int|null                                                                                    $revision_history_count
 */
class CareplanAssessment extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public function approver()
    {
        return $this->belongsTo(User::class, 'provider_approver_id');
    }

//    DO NOT UNCOMMENT OUT. THIS IS ACTUALLY STORING USER IDS.
//    public function carePlan()
//    {
//        return $this->belongsTo(CarePlan::class, 'careplan_id', 'user_id');
//    }

    public function note()
    {
        $patient  = $this->patient()->first();
        $approver = $this->approver()->first();

        return 'Patient '.$this->careplan_id.' was enrolled by Dr. '.
                    $approver->display_name.' on '.Carbon::parse($this->updated_at)->format('m/d/Y').' at '.
                        Carbon::parse($this->updated_at)->format('H:i:s');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'careplan_id');
    }

    public function process($object)
    {
        foreach (get_object_vars($object) as $key => $value) {
            if ('_token' != $key) {
                $this->$key = $value;
            }
        }
        if ($this->diabetes_screening_risk) {
            $this->diabetes_screening_risk = json_encode(array_values($this->diabetes_screening_risk));
        }
        if ($this->patient_functional_assistance_areas) {
            $this->patient_functional_assistance_areas = json_encode(array_values($this->patient_functional_assistance_areas));
        }
        if ($this->patient_psychosocial_areas_to_watch) {
            $this->patient_psychosocial_areas_to_watch = json_encode(array_values($this->patient_psychosocial_areas_to_watch));
        }
        if ($this->risk_factors) {
            $this->risk_factors = json_encode(array_values($this->risk_factors));
        }

        //adjust for case where values are empty strings
        if ('' == $this->diabetes_screening_risk) {
            $this->diabetes_screening_risk = '[]';
        }
        if ('' == $this->patient_functional_assistance_areas) {
            $this->patient_functional_assistance_areas = '[]';
        }
        if ('' == $this->patient_psychosocial_areas_to_watch) {
            $this->patient_psychosocial_areas_to_watch = '[]';
        }
        if ('' == $this->risk_factors) {
            $this->risk_factors = '[]';
        }
    }

    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @param mixed $notifiable
     */
    public function toPdf($notifiable): string
    {
        $patient  = $this->patient()->first();
        $approver = $this->approver()->first();

        $pdf = app('snappy.pdf.wrapper');
        $pdf->loadView('emails.assessment-created', [
            'assessment' => $this,
            'notifiable' => $notifiable,
        ]);

        $this->fileName = Carbon::now()->toDateString().'-'.$patient['id'].'.pdf';
        $filePath       = base_path('storage/pdfs/assessments/'.$this->fileName);
        $pdf->save($filePath, true);

        return $filePath;
    }

    public function unload()
    { // decode these values from json
        if (is_string($this->diabetes_screening_risk)) {
            $this->diabetes_screening_risk = json_decode($this->diabetes_screening_risk);
        }
        if (is_string($this->patient_functional_assistance_areas)) {
            $this->patient_functional_assistance_areas = json_decode($this->patient_functional_assistance_areas);
        }
        if (is_string($this->patient_psychosocial_areas_to_watch)) {
            $this->patient_psychosocial_areas_to_watch = json_decode($this->patient_psychosocial_areas_to_watch);
        }
        if (is_string($this->risk_factors)) {
            $this->risk_factors = json_decode($this->risk_factors);
        }

        return $this;
    }
}
