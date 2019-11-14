<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface RelatesToActivity
{
    /**
     * Return a call object.
     *
     * @return mixed
     */
    public function getActivities();

    /**
     *  Many "read only" Activities(in this case = addendums) might exist for the same note.
     *  We are marking all Activities(type addendum) for the same note as read.
     *
     * @return mixed
     */
    public function markActivitiesAsDone();

    /**
     * Many "read only" Notifications(in this case = addendums) might exist for the same note.
     * We are marking all Notifications(type addendum) for the same note as read.
     *
     * @return mixed
     */
    public function markNotificationsForActivitiesAsRead();
}
