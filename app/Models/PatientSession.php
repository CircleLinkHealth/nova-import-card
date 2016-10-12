<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSession extends Model
{
    public $fillable = [
        'user_id',
        'patient_id'
    ];
}
