<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Entities;

/**
 * App\DatabaseNotification.
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
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification hasAttachmentType($type)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification hasNotifiableType($type)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereAttachmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereAttachmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereNotifiableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereNotifiableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DatabaseNotification extends \Illuminate\Notifications\DatabaseNotification
{
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
}
