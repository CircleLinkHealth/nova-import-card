<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Database\Eloquent\Model;

/**
 * App\LoginLogout.
 *
 * @property int                                                                                          $id
 * @property int|null                                                                                     $user_id
 * @property \dateTime|null                                                                               $login_time
 * @property \dateTime|null                                                                               $logout_time
 * @property int                                                                                          $duration_in_sec
 * @property string                                                                                       $ip_address
 * @property int                                                                                          $was_edited
 * @property \Illuminate\Support\Carbon|null                                                              $created_at
 * @property \Illuminate\Support\Carbon|null                                                              $updated_at
 * @property \CircleLinkHealth\TimeTracking\Entities\PageTimer[]|\Illuminate\Database\Eloquent\Collection $activities
 * @property int|null                                                                                     $activities_count
 * @property \CircleLinkHealth\Customer\Entities\User|null                                                $user
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout newModelQuery()
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout newQuery()
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout query()
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout whereCreatedAt($value)
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout whereDurationInSec($value)
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout whereId($value)
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout whereIpAddress($value)
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout whereLoginTime($value)
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout whereLogoutTime($value)
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout whereUpdatedAt($value)
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout whereUserId($value)
 * @method   static                                                                                       \Illuminate\Database\Eloquent\Builder|\App\LoginLogout whereWasEdited($value)
 * @mixin \Eloquent
 */
class LoginLogout extends Model
{
    protected $casts = [
        'login_time'  => 'date',
        'logout_time' => 'date',
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
