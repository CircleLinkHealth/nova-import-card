<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'name' => 'Core',

    'is_production_env'   => env('IS_PRODUCTION_SERVER', false),
    'is_queue_worker_env' => env('IS_QUEUE_WORKER_SERVER', false),
];
