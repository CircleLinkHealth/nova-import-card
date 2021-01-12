<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RedirectToVaporRequest extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['user_id', 'token'];
}
