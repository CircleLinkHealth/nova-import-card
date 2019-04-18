<?php

namespace App;

class PersonalizedPreventionPlan extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = [
        'user_id',
        'display_name',
        'birth_date',
        'address',
        'billing_provider',
        'rec_task_title',
        'hra_answers',
        'vitals_answers',
        'answers_for_eval',
    ];

    protected $casts = [
        'vitals_answers'   => 'array',
        'hra_answers'      => 'array',
        'answers_for_eval' => 'array',

    ];
    protected $table = 'personalized_prevention_plan';

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
