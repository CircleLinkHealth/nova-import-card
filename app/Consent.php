<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientConsent extends Model
{
    protected $fillable = [
        'chargeable_service_id',
        'user_id',
        'consented_at',
    ];

    protected $dates = [
        'consented_at',
        'created_at',
        'updated_at',
    ];
}
