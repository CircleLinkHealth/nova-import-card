<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

/*
|--------------------------------------------------------------------------
| Aprima CCD API Routes
|--------------------------------------------------------------------------
|
| General Notes
| Authentication is handled with https://github.com/tymondesigns/jwt-auth
|
|
*/

Route::group([
    'prefix' => 'v1.0',
], function () {
    //Should change this to a GET to make this RESTful
    Route::post('oauth/access_token', 'CcdApi\Aprima\AuthController@getAccessToken');

    Route::group(['middleware' => 'aprima.ccdapi.auth.adapter'], function () {
        //Should make this plural
        Route::post('ccd', [
            'uses' => 'CcdApi\Aprima\CcdApiController@uploadCcd',
            'as'   => 'api.aprima.uploadCcd',
        ]);
        Route::get('reports', 'CcdApi\Aprima\CcdApiController@reports');

        Route::get('notes', 'CcdApi\Aprima\CcdApiController@notes');

        //Let's make things RESTful from here onwards
        Route::get('ccm-times', 'CcdApi\Aprima\CcdApiController@getCcmTime');
    });
});

Route::group(['middleware' => 'auth:api'], function () {
});
