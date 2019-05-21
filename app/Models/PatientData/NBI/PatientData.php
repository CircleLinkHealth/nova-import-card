<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\NBI;

use Illuminate\Database\Eloquent\Model;

class PatientData extends Model
{
    protected $dates = [
        'dob',
    ];

    protected $fillable = [
        'dob',
        'first_name',
        'last_name',
        'mrn',
        'primary_insurance',
        'provider',
        'secondary_insurance',
    ];
    protected $table = 'patient_data';
}
