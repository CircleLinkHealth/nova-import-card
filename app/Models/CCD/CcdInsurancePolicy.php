<?php

namespace App\Models\CCD;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CcdInsurancePolicy extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'name',
        'type',
        'policy_id',
        'relation',
        'subscriber',
        'approved'
    ];
}
