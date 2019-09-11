<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginLogout extends Model
{
    protected $casts = [
        'login_time'  => 'dateTime',
        'logout_time' => 'dateTime',
    ];
    protected $fillable = [
        'user_id',
        'login_time',
        'logout_time',
        'ip_address',
    ];

    protected $table = 'login_logout_events';
}
