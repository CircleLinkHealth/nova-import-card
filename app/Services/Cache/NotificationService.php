<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/7/18
 * Time: 6:40 PM
 */

namespace App\Services\Cache;

use App\Repositories\Cache\UserNotificationList;
use App\User;

class NotificationService
{
    public function notifyAdmins($title, $description, $link, $linkTitle)
    {
        User::ofType('administrator')
            ->get()
            ->map(function ($userId) use ($title, $description, $link, $linkTitle) {
                $userNotification = new UserNotificationList($userId);

                $userNotification->push($title, $description, $link, $linkTitle);
            });
    }
}
