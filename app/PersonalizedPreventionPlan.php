<?php

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

class PersonalizedPreventionPlan extends BaseModel
{
    protected $fillable = [
        'user_id',
        'hra_instance_id',
        'vitals_instance_id',
        'hra_answers',
        'vitals_answers',
        'answers_for_eval',
    ];

    protected $casts = [
        'vitals_answers'   => 'array',
        'hra_answers'      => 'array',
        'answers_for_eval' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $table = 'personalized_prevention_plan';

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
