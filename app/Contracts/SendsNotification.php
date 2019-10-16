<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use Illuminate\Notifications\Notification;

interface SendsNotification
{
    public function getNotifiable();

    public function getNotification(): Notification;

    public function send();
}
