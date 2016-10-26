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




/*
|--------------------------------------------------------------------------
| Legacy Routes
|--------------------------------------------------------------------------
|
| General Notes
| We should probably remove those. It's routes for our app.
|
|
*/

// JWTauth Login
//Route::post('api/v2.1/login', 'AuthorizationController@login');

// JWTauth api routes
//Route::group([
//    'before'     => 'jwt-auth',
//    'prefix'     => 'wp/api/v2.1',
//    'middleware' => 'authApiCall',
//], function () {
//    // return token data, initial test
//    Route::post('tokentest', 'AuthorizationController@tokentest');
//
//    Route::group([
//        'prefix' => 'password',
//    ], function () {
//        Route::get('broker', 'Auth\PasswordController@getBroker');
//        Route::post('email', 'Auth\PasswordController@postEmail');
//        Route::get('email', 'Auth\PasswordController@getEmail');
//        Route::get('reset', 'Auth\PasswordController@getReset');
//        Route::post('reset', 'Auth\PasswordController@postReset');
//    });
//
//    // return data on logged in user
//    Route::post('user', 'UserController@index');
//    Route::get('user', 'UserController@index');
//
//    // observations
//    Route::post('comment', 'CommentController@store');
//    Route::post('observation', 'ObservationController@store');
//    Route::get('careplan', 'CareplanController@show');
//    Route::get('reports/progress', 'ReportsController@progress');
//    Route::get('reports/careplan', 'ReportsController@careplan');
//
//
//    // locations
//    Route::get('locations', 'LocationController@index');
//});