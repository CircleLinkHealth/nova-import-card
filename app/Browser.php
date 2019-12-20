<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Browser extends Model
{
    protected $dates = [
        'release_date',
    ];
    protected $fillable = [
        'name',
        'warning_version',
        'required_version',
        'release_date',
    ];
}
