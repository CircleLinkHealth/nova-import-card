<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
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
        'was_edited',
        'duration_in_sec',
    ];

    protected $table = 'login_logout_events';

    public function activities()
    {
        return $this->hasMany(PageTimer::class, 'provider_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
