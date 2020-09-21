<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::middleware('auth')->group(function () {
    Route::group(['prefix' => '2fa'], function () {
        Route::get('', [
            'uses' => 'AuthyController@showVerificationTokenForm',
            'as'   => 'user.2fa.show.token.form',
        ]);
    });

    Route::group(['prefix' => 'api/2fa'], function () {
        Route::group(['prefix' => 'token'], function () {
            Route::post('sms', [
                'uses' => 'AuthyController@sendTokenViaSms',
                'as'   => 'user.2fa.token.sms',
            ]);

            Route::post('voice', [
                'uses' => 'AuthyController@sendTokenViaVoice',
                'as'   => 'user.2fa.token.voice',
            ]);

            Route::post('verify', [
                'uses' => 'AuthyController@verifyToken',
                'as'   => 'user.2fa.token.verify',
            ]);
        });
        Route::group(['prefix' => 'one-touch-request'], function () {
            Route::post('create', [
                'uses' => 'AuthyController@createOneTouchRequest',
                'as'   => 'user.2fa.one-touch-request.create',
            ]);

            Route::post('check-status', [
                'uses' => 'AuthyController@checkOneTouchRequestStatus',
                'as'   => 'user.2fa.one-touch-request.check',
            ]);
        });
    });

    Route::group(['prefix' => 'api/account-settings'], function () {
        Route::group(['prefix' => '2fa'], function () {
            Route::post('qr-code', [
                'uses' => 'AuthyController@generateQrCode',
                'as'   => 'user.2fa.token.qr-code',
            ]);

            Route::post('', [
                'uses' => 'AuthyController@store',
                'as'   => 'user.2fa.store',
            ]);
        });
    });
    
    Route::group(['prefix' => 'settings'], function () {
        Route::get('', [
            'uses' => 'UserSettingsController@show',
            'as'   => 'user.settings.manage',
        ]);
    });
});
