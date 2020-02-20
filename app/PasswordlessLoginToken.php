<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

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
        return $this->hasOne(User::class, 'email', 'email');
    }

    /**
     * @param $token
     *
     * @return User|null
     */
    public static function userFromToken($token)
    {
        $query = self::where('token', $token)
            ->with('user')
            ->first();

        return $query->user ?? null;
    }
}
