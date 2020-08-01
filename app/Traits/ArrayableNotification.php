<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

trait ArrayableNotification
{
    /**
     * @param $notifiable
     */
    public function notificationData($notifiable): array
    {
        return [
            'redirect_link' => $this->redirectLink($notifiable),
            'description'   => $this->description($notifiable),
            'subject'       => $this->getSubject($notifiable),
        ];
    }
}
