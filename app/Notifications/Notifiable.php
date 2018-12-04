<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Notifications\RoutesNotifications;

trait Notifiable
{
    use HasDatabaseNotifications, RoutesNotifications;
}
