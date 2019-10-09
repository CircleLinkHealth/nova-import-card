<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
     * Live Notifications feature was launched in October 2019.
     * To avoid showing all notifications until that date as unread, we only fetch notifications after this date for live count.
     */
    'only_show_notifications_created_after' => \Carbon\Carbon::create(2019, 10, 1, 0, 0, 0),

    /**
     * Only show notifications of the following classes in Live Notifications count.
     */
    'classes' => [
        //        App\Notifications\AddendumCreated::class,
    ],
];
