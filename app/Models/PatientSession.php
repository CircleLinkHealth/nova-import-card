<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSession extends \App\BaseModel
{
    public $fillable = [
        'user_id',
        'patient_id'
    ];
}
