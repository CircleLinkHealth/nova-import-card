<?php

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

class PersonalizedPreventionPlan extends BaseModel
{
    protected $fillable = [
        'user_id',
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
        return $this->belongsTo(User::class, 'user_id');
    }
}
