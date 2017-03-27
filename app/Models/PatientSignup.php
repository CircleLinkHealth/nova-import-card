<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSignup extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'comment',
    ];


}
