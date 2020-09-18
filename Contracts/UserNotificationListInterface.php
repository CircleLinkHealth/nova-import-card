<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Contracts;

interface UserNotificationListInterface
{
    /**
     * Get the User's cached views.
     *
     * @param int $start
     * @param int $end
     *
     * @return static
     */
    public function all($start = 0, $end = -1);

    /**
     * Returns the count of the User's Notifications.
     *
     * @return mixed
     */
    public function count();

    public function delete($notification);

    /**
     * Add a Notification to the User's Notification List.
     *
     * @param string $title
     * @param string $description
     * @param null   $link
     * @param string $linkTitle
     */
    public function push($title = '', $description = '', $link = null, $linkTitle = 'Link');

    /**
     * Get the hash key for the give User's cached views list.
     *
     * @return string
     */
    public function userHashKey();
}
