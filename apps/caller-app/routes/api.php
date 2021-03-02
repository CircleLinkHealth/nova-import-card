<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return app()->version();
});

//fixme: remove after deployment
Route::get('/throw-exception', function () {
    throw new Exception('testing');
});

Route::group([
    'prefix'     => 'twilio',
    'middleware' => 'auth',
], function () {
    Route::get('/token', [
        'uses' => 'TwilioController@obtainToken',
        'as'   => 'twilio.token',
    ]);
    Route::post('/call/js-create-conference', [
        'uses' => 'TwilioController@jsCreateConference',
        'as'   => 'twilio.js.create.conference',
    ]);
    Route::post('/call/get-conference-info', [
        'uses' => 'TwilioController@getConferenceInfo',
        'as'   => 'twilio.js.get.conference.info',
    ]);
    Route::post('/call/join-conference', [
        'uses' => 'TwilioController@joinConference',
        'as'   => 'twilio.call.join.conference',
    ]);
    Route::post('/call/end', [
        'uses' => 'TwilioController@endCall',
        'as'   => 'twilio.call.leave.conference',
    ]);
});

Route::group([
    'prefix' => 'twilio',
], function () {
    Route::post('/call/place', [
        'uses' => 'TwilioController@placeCall',
        'as'   => 'twilio.call.place',
    ]);
    Route::post('/call/status', [
        'uses' => 'TwilioController@callStatusCallback',
        'as'   => 'twilio.call.status',
    ]);
    Route::post('/call/number-status', [
        'uses' => 'TwilioController@dialNumberStatusCallback',
        'as'   => 'twilio.call.number.status',
    ]);
    Route::post('/call/dial-action', [
        'uses' => 'TwilioController@dialActionCallback',
        'as'   => 'twilio.call.dial.action',
    ]);
    Route::post('/call/conference-status', [
        'uses' => 'TwilioController@conferenceStatusCallback',
        'as'   => 'twilio.call.conference.status',
    ]);
    Route::post('/call/recording-status', [
        'uses' => 'TwilioController@recordingStatusCallback',
        'as'   => 'twilio.call.recording.status',
    ]);
    Route::post('/debugger-webhook', [
        'uses' => 'TwilioController@debuggerWebhook',
        'as'   => 'twilio.debugger.webhook',
    ]);
});

Route::get('health-check', 'HealthCheckController@isSiteUp');
