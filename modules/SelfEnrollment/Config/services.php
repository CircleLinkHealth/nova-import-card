<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'awv' => [
        'url'        => env('AWV_URL', ''),
        'report_url' => env('AWV_URL', '').env('AWV_REPORT_URI', ''),
    ],
];
