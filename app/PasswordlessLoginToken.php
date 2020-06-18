<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\PasswordlessLoginToken.
 *
 * @property int                                      $id
 * @property int                                      $user_id
 * @property string                                   $token
 * @property \Illuminate\Support\Carbon|null          $created_at
 * @property \Illuminate\Support\Carbon|null          $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PasswordlessLoginToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PasswordlessLoginToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PasswordlessLoginToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PasswordlessLoginToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PasswordlessLoginToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PasswordlessLoginToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PasswordlessLoginToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PasswordlessLoginToken whereUserId($value)
 * @mixin \Eloquent
 */
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
     */
    public static function userFromToken($token): ?User
    {
        $query = self::where('token', $token)
            ->with('user')
            ->first();

        return $query->user ?? null;
    }
}
