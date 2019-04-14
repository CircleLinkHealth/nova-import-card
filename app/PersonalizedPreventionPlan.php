<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonalizedPreventionPlan extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = [
        'user_id',
        'display_name',
        'birth_date',
        'address',
        'billing_provider',
        'rec_task_title',
        'hra',
        'vitals'
    ];

    protected $table = 'personalized_prevention_plan';

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
