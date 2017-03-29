<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSignup extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'comment',
    ];


}
