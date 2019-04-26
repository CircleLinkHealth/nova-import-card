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
        'circlelink-awv-1.medstack.net',
        '54.156.98.138', //awv ip
        '18.205.240.109', //worker ip
    ]
    : [
        '.*ngrok.io',
        '.*dev',
        '.*test',
        '.*careplanmanager.com',
        'circlelink-production.medstack.net',
        'circlelink-worker.medstack.net',
        'circlelink-awv-1.medstack.net',
    ],
];
