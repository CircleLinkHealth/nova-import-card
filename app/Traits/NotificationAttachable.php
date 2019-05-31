<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

trait NotificationAttachable
{
    /**
     * Returns the notifications that included this model as an attachment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(\CircleLinkHealth\Core\Entities\DatabaseNotification::class, 'attachment')
            ->orderBy('created_at', 'desc');
    }
}
