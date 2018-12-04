<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/2/18
 * Time: 1:46 PM
 */

namespace App\Repositories\Cache;

use App\Contracts\UserNotificationListInterface;

/**
 * This is an empty class that is returned instead of UserNotificationList on the saas environment where there is no cache setup
 *
 * Class EmptyUserNotificationList
 * @package App\Repositories\Cache
 */
class EmptyUserNotificationList implements UserNotificationListInterface
{

    /**
     * Add a Notification to the User's Notification List
     *
     * @param string $title
     * @param string $description
     * @param null $link
     * @param string $linkTitle
     */
    public function push($title = '', $description = '', $link = null, $linkTitle = 'Link')
    {
        // TODO: Implement push() method.
    }

    /**
     * Get the hash key for the give User's cached views list
     *
     *
     * @return string
     */
    public function userHashKey()
    {
        // TODO: Implement userHashKey() method.
    }

    /**
     * Returns the count of the User's Notifications
     *
     * @return mixed
     */
    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * Get the User's cached views
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

    public function delete($notification)
    {
        // TODO: Implement delete() method.
    }
}
