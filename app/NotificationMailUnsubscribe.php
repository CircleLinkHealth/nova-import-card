<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\NotificationMailUnsubscribe.
 *
 * @property int                             $id
 * @property int|null                        $user_id
 * @property string|null                     $notification_type
 * @property string|null                     $unsubscribed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null                     $deleted_at
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\NotificationMailUnsubscribe onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe whereNotificationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe whereUnsubscribedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\NotificationMailUnsubscribe withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\NotificationMailUnsubscribe withoutTrashed()
 * @mixin \Eloquent
 *
 * @property int|null $channel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NotificationMailUnsubscribe whereChannel($value)
 */
class NotificationMailUnsubscribe extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'notification_type',
        'unsubscribed_at',
    ];

    protected $table = 'unsubscriptions_notification_mail';
}
