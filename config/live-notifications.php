<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'only_show_notifications_created_after' => \Carbon\Carbon::create(2019, 9, 1, 0, 0, 0),
    'classes'                               => [
        App\Notifications\AddendumCreated::class,
    ],
];
