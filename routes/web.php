<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

Route::get('/', function () {
    return app()->version();
});

//fixme: remove after deployment
Route::get('/throw-exception', function () {
    throw new Exception("testing");
});

Route::group([
    'prefix'     => 'twilio',
    'middleware' => 'auth',
], function () {
    Route::get('/token', [
        'uses' => 'Twilio\TwilioController@obtainToken',
        'as'   => 'twilio.token',
    ]);
    Route::post('/call/js-create-conference', [
        'uses' => 'Twilio\TwilioController@jsCreateConference',
        'as'   => 'twilio.js.create.conference',
    ]);
    Route::post('/call/get-conference-info', [
        'uses' => 'Twilio\TwilioController@getConferenceInfo',
        'as'   => 'twilio.js.get.conference.info',
    ]);
    Route::post('/call/join-conference', [
        'uses' => 'Twilio\TwilioController@joinConference',
        'as'   => 'twilio.call.join.conference',
    ]);
    Route::post('/call/end', [
        'uses' => 'Twilio\TwilioController@endCall',
        'as'   => 'twilio.call.leave.conference',
    ]);
});

Route::group([
    'prefix' => 'twilio',
], function () {
    Route::post('/call/place', [
        'uses' => 'Twilio\TwilioController@placeCall',
        'as'   => 'twilio.call.place',
    ]);
    Route::post('/call/status', [
        'uses' => 'Twilio\TwilioController@callStatusCallback',
        'as'   => 'twilio.call.status',
    ]);
    Route::post('/call/number-status', [
        'uses' => 'Twilio\TwilioController@dialNumberStatusCallback',
        'as'   => 'twilio.call.number.status',
    ]);
    Route::post('/call/dial-action', [
        'uses' => 'Twilio\TwilioController@dialActionCallback',
        'as'   => 'twilio.call.dial.action',
    ]);
    Route::post('/call/conference-status', [
        'uses' => 'Twilio\TwilioController@conferenceStatusCallback',
        'as'   => 'twilio.call.conference.status',
    ]);
    Route::post('/call/recording-status', [
        'uses' => 'Twilio\TwilioController@recordingStatusCallback',
        'as'   => 'twilio.call.recording.status',
    ]);
    Route::post('/debugger-webhook', [
        'uses' => 'Twilio\TwilioController@debuggerWebhook',
        'as'    => 'twilio.debugger.webhook',
    ]);
});

Route::get('health-check', 'HealthCheckController@isSiteUp');
