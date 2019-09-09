<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginLogout extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'ip_address',
    ];

    protected $table = 'login_logout_events';
}
