<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::prefix('admin')->group(
    function () {
        Route::prefix('users')->group(
            function () {
                Route::get(
                    '',
                    [
                        'uses' => 'SuperAdmin\UserController@index',
                        'as'   => 'admin.users.index',
                    ]
                )->middleware('permission:user.read,practice.read');
                Route::post(
                    '',
                    [
                        'uses' => 'SuperAdmin\UserController@store',
                        'as'   => 'admin.users.store',
                    ]
                )->middleware('permission:user.create');
                Route::get(
                    'create',
                    [
                        'uses' => 'SuperAdmin\UserController@create',
                        'as'   => 'admin.users.create',
                    ]
                )->middleware('permission:user.read,practice.read,location.read,role.read');
                Route::get(
                    'doAction',
                    [
                        'uses' => 'SuperAdmin\UserController@doAction',
                        'as'   => 'admin.users.doAction',
                    ]
                );
                Route::get(
                    '{id}/edit',
                    [
                        'uses' => 'SuperAdmin\UserController@edit',
                        'as'   => 'admin.users.edit',
                    ]
                )->middleware('permission:user.read,practice.read,location.read,role.read');
                Route::get(
                    '{id}/destroy',
                    [
                        'uses' => 'SuperAdmin\UserController@destroy',
                        'as'   => 'admin.users.destroy',
                    ]
                )->middleware('permission:user.delete');
                Route::post(
                    '{id}/edit',
                    [
                        'uses' => 'SuperAdmin\UserController@update',
                        'as'   => 'admin.users.update',
                    ]
                )->middleware('permission:user.update');
            }
        );
    }
);
