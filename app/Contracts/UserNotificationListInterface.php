<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/2/18
 * Time: 1:44 PM
 */

namespace App\Contracts;

interface UserNotificationListInterface
{
    /**
     * Add a Notification to the User's Notification List
     *
     * @param string $title
     * @param string $description
     * @param null $link
     * @param string $linkTitle
     */
    public function push($title = '', $description = '', $link = null, $linkTitle = 'Link');

    /**
     * Get the hash key for the give User's cached views list
     *
     *
     * @return string
     */
    public function userHashKey();

    /**
     * Returns the count of the User's Notifications
     *
     * @return mixed
     */
    public function count();

    /**
     * Get the User's cached views
     *
     * @param int $start
     * @param int $end
     *
     * @return static
     */
    public function all($start = 0, $end = -1);

    public function delete($notification);
}
