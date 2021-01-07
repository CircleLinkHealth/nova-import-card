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

    Route::get('staff', [
        'uses' => 'RedirectToAdminApp@getCreatePracticeStaff',
        'as'   => 'provider.dashboard.manage.staff',
    ]);

    Route::get('chargeable-services', [
        'uses' => 'RedirectToAdminApp@getPracticeChargeableServices',
        'as'   => 'provider.dashboard.manage.chargeable-services',
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

Route::get('admin/users/{id}/destroy', [
    'uses' => 'RedirectToAdminApp@destroyUser',
    'as'   => 'admin.users.destroy',
])->middleware('permission:user.delete');

Route::get('direct-mail/show/{dmId}', [
    'uses' => 'RedirectToAdminApp@dmShow',
    'as'   => 'direct-mail.show',
]);
