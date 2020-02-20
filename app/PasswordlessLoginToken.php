<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

class PasswordlessLoginToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token',
    ];

    /**
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param $token
     *
     * @return User|null
     */
    public static function userFromToken($token) :?User
    {
        $query = self::where('token', $token)
            ->with('user')
            ->first();

        return $query->user ?? null;
    }
}
