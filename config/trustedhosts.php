<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'hosts' => isProductionEnv()
        ? [
            '.*careplanmanager.com',
            'circlelink-production.medstack.net',
            'circlelink-worker.medstack.net',
        ]
        : [
            '.*ngrok.io',
            '.*dev',
            '.*test',
            '.*careplanmanager.com',
            'circlelink-production.medstack.net',
            'circlelink-worker.medstack.net',
        ],
];
