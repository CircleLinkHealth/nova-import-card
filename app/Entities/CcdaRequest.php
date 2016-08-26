<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class CcdaRequest extends Model implements Transformable
{
    use TransformableTrait;

    protected $fillable = [
        'ccda_id',
        'vendor',
        'patient_id',
        'department_id',
        'successful_call',
    ];

}
