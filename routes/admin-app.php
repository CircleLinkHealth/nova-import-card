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

Route::post('callupdate', [
    'uses' => '\CircleLinkHealth\CpmAdmin\Http\Controllers\CallController@update',
    'as'   => 'api.callupdate',
]);
Route::post('callcreate-multi', [
    'uses' => '\CircleLinkHealth\CpmAdmin\Http\Controllers\CallController@createMulti',
    'as'   => 'api.callcreate-multi',
]);
