<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::group([
    'prefix'     => 'practices/{practiceSlug}',
    'middleware' => [
        'auth',
    ],
], function () {
    Route::get('notifications', [
        'uses' => 'RedirectToAdminApp@getCreateNotifications',
        'as'   => 'provider.dashboard.manage.notifications',
    ]);

    Route::get('practice', [
        'uses' => 'RedirectToAdminApp@getCreatePractice',
        'as'   => 'provider.dashboard.manage.practice',
    ]);
});

Route::get('calls-v2', [
    'uses' => 'RedirectToAdminApp@getPAM',
    'as'   => 'patientCallManagement.v2.provider.index',
]);

Route::get('ca/index', [
    'uses' => 'RedirectToAdminApp@getCADirectorIndex',
    'as'   => 'ca-director.provider.index',
]);

Route::get('admin/nurses/windows', [
    'uses' => 'RedirectToAdminApp@getAdminNurseSchedules',
    'as'   => 'get.admin.nurse.schedules',
]);
