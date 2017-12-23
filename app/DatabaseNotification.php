<?php

namespace App;


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
     * Scope notifications by a specific attachment type
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
     * Scope notifications by a specific notifiable type
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