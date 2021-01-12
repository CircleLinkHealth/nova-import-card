<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    // Path to a json file containing the credentials of a Google Service account.
    'client_secret_json' => storage_path('google-calendar/credentials.json'),

    // The id of the Google Calendar that will be used by default.
    'calendar_id' => env('GOOGLE_CALENDAR_ID'),
];
