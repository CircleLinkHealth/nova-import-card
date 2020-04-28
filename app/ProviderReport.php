<?php

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

class ProviderReport extends BaseModel
{
    protected $fillable = [
        'user_id',
        'hra_instance_id',
        'vitals_instance_id',
        'reason_for_visit',
        'demographic_data',
        'allergy_history',
        'medical_history',
        'medication_history',
        'family_medical_history',
        'immunization_history',
        'screenings',
        'mental_state',
        'vitals',
        'diet',
        'social_factors',
        'sexual_activity',
        'exercise_activity_levels',
        'functional_capacity',
        'current_providers',
        'advanced_care_planning',
        'specific_patient_requests',
    ];

    protected $casts = [
        'demographic_data'          => 'array',
        'allergy_history'           => 'array',
        'medical_history'           => 'array',
        'medication_history'        => 'array',
        'family_medical_history'    => 'array',
        'immunization_history'      => 'array',
        'screenings'                => 'array',
        'mental_state'              => 'array',
        'vitals'                    => 'array',
        'diet'                      => 'array',
        'social_factors'            => 'array',
        'sexual_activity'           => 'array',
        'functional_capacity'       => 'array',
        'current_providers'         => 'array',
        'exercise_activity_levels'  => 'array',
        'advanced_care_planning'    => 'array',
        'specific_patient_requests' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hraSurveyInstance()
    {
        return $this->hasOne(SurveyInstance::class, 'id', 'hra_instance_id');
    }

    public function vitalsSurveyInstance()
    {
        return $this->hasOne(SurveyInstance::class, 'id', 'vitals_instance_id');
    }

    public function scopeForYear($query, $year)
    {
        if (is_a($year, 'Carbon\Carbon')) {
            $year = $year->year;
        }

        return $query->whereHas('hraSurveyInstance', function ($hra) use ($year) {
            $hra->where('year', $year);
        })
                     ->whereHas('vitalsSurveyInstance', function ($vitals) use ($year) {
                         $vitals->where('year', $year);
                     });
    }
}
