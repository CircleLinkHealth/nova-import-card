<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Contracts;

interface RelatesToActivity
{
    /**
     * Returns a Call collection.
     *
     * @return mixed
     */
    public function getActivities();

    /**
     *  Changes the status of all Activities (App\Call) related to this Model to "Done".
     *
     * @return mixed
     */
    public function markActivitiesAsDone();

    /**
     * Changes the status of all Notifications (DatabaseNotifications) related to this Model to "read(date)".
     *
     * @return mixed
     */
    public function markAllAttachmentNotificationsAsRead();
}
