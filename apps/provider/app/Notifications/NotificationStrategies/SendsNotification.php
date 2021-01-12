<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\NotificationStrategies;

use Illuminate\Notifications\Notification;

abstract class SendsNotification
{
    public function send()
    {
        return $this->getNotifiable()->notify($this->getNotification());
    }

    abstract protected function getNotifiable();

    abstract protected function getNotification(): Notification;
}
