<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
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
        Route::post('ccd', 'CcdApi\Aprima\CcdApiController@uploadCcd');
        Route::get('reports', 'CcdApi\Aprima\CcdApiController@reports');

        Route::get('notes', 'CcdApi\Aprima\CcdApiController@notes');

        //Let's make things RESTful from here onwards
        Route::get('ccm-times', 'CcdApi\Aprima\CcdApiController@getCcmTime');
    });
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'admin'], function () {
        Route::resource('calls', 'API\Admin\CallsController');

        Route::resource('user.outbound-calls', 'API\UserOutboundCallController');
    });
});
