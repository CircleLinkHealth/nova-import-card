<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'biometrics' => [
        \App\Models\CPM\Biometrics\CpmWeight::class,
        \App\Models\CPM\Biometrics\CpmBloodPressure::class,
        \App\Models\CPM\Biometrics\CpmBloodSugar::class,
        \App\Models\CPM\Biometrics\CpmSmoking::class,
    ],
];
