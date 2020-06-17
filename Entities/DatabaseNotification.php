<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Entities;

use App\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;

/**
 * CircleLinkHealth\Core\Entities\DatabaseNotification.
 *
 * @property string                                        $id
 * @property string                                        $type
 * @property int                                           $notifiable_id
 * @property string                                        $notifiable_type
 * @property int|null                                      $attachment_id
 * @property string|null                                   $attachment_type
 * @property array                                         $data
 * @property \Illuminate\Support\Carbon|null               $read_at
 * @property \Illuminate\Support\Carbon|null               $created_at
 * @property \Illuminate\Support\Carbon|null               $updated_at
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model $attachment
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model $notifiable
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification hasAttachmentType($type)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification hasNotifiableType($type)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification newModelQuery()
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification newQuery()
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification query()
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification whereAttachmentId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification whereAttachmentType($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification whereCreatedAt($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification whereData($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification whereId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification whereNotifiableId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification whereNotifiableType($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification whereReadAt($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification whereType($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification liveNotification()
 * @method static \Illuminate\Notifications\DatabaseNotificationCollection|static[] all($columns = ['*'])
 * @method static \Illuminate\Notifications\DatabaseNotificationCollection|static[] get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\DatabaseNotification selfEnrollmentInvites()
 */
class DatabaseNotification extends \Illuminate\Notifications\DatabaseNotification
{
    protected $casts = [
        'data' => 'array',
    ];
    protected $dates = [
        'read_at',
    ];

    /**
     * Get the attachment that was send with this notification.
     */
    public function attachment()
    {
        return $this->morphTo();
    }

    /**
     * Scope notifications by a specific attachment type.
     *
     * @param $builder
     * @param $type | Fully qualified class name (eg. User::class)
     *
     * @return mixed
     */
    public function scopeHasAttachmentType($builder, $type)
    {
        return $builder->where('attachment_type', '=', $type);
    }

    /**
     * Scope notifications by a specific notifiable type.
     *
     * @param $builder
     * @param $type | Fully qualified class name (eg. User::class)
     *
     * @return mixed
     */
    public function scopeHasNotifiableType($builder, $type)
    {
        return $builder->where('notifiable_type', '=', $type);
    }

    /**
     * @param $builder
     *
     * @return mixed
     */
    public function scopeLiveNotification($builder)
    {
        return $builder->where('created_at', '>=', config('live-notifications.only_show_notifications_created_after'))
            ->whereIn('type', config('live-notifications.classes'));
    }

    public function scopeSelfEnrollmentInvites($builder)
    {
        return $builder->where(function ($q) {
            //legacy and new classes
            return $q->whereIn('type', ['App\Notifications\SendEnrollementSms', 'App\Notifications\SendEnrollmentEmail', SelfEnrollmentInviteNotification::class]);
        });
    }
}
