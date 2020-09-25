<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\SmsExclusion.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property User                            $user
 * @method   static                          \Illuminate\Database\Eloquent\Builder|NotificationsExclusion newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|NotificationsExclusion newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|NotificationsExclusion query()
 * @mixin \Eloquent
 * @property bool $sms
 * @property bool $mail
 */
class NotificationsExclusion extends Model
{
    protected $casts = [
        'sms'  => 'boolean',
        'mail' => 'boolean',
    ];
    protected $fillable = [
        'user_id',
        'sms',
        'mail',
    ];
    protected $table = 'notifications_exclusions';

    public static function isMailBlackListed($userId): bool
    {
        return self::isBlackListed($userId, 'mail');
    }

    public static function isSmsBlackListed($userId): bool
    {
        return self::isBlackListed($userId, 'sms');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    private static function isBlackListed($userId, string $channel): bool
    {
        return self::where('user_id', '=', $userId)
            ->where($channel, '=', true)
            ->exists();
    }
}
