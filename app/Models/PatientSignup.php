<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSignup extends \App\BaseModel
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'comment',
    ];
}
