<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories\Cache;

use CircleLinkHealth\Customer\Contracts\UserNotificationListInterface;

/**
 * This is an empty class that is returned instead of UserNotificationList on the saas environment where there is no cache setup.
 *
 * Class EmptyUserNotificationList
 */
class EmptyUserNotificationList implements UserNotificationListInterface
{
    /**
     * Get the User's cached views.
     *
     * @param int $start
     * @param int $end
     *
     * @return static
     */
    public function all($start = 0, $end = -1)
    {
        return [];
    }

    /**
     * Returns the count of the User's Notifications.
     *
     * @return mixed
     */
    public function count()
    {
        // TODO: Implement count() method.
    }

    public function delete($notification)
    {
        // TODO: Implement delete() method.
    }

    /**
     * Add a Notification to the User's Notification List.
     *
     * @param string $title
     * @param string $description
     * @param null   $link
     * @param string $linkTitle
     */
    public function push($title = '', $description = '', $link = null, $linkTitle = 'Link')
    {
        // TODO: Implement push() method.
    }

    /**
     * Get the hash key for the give User's cached views list.
     *
     * @return string
     */
    public function userHashKey()
    {
        // TODO: Implement userHashKey() method.
    }
}
