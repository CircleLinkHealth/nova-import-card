<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\ToledoClinic;

use CircleLinkHealth\Core\Entities\BaseModel;

class Medications extends BaseModel
{
    protected $fillable = [
        'patient_id',
        'name',
        'sig',
        'start',
        'stop',
        'status',
    ];
    protected $table = 'toledo-clinic_medications';
}
