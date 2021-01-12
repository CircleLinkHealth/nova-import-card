<?php

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

//fixme: remove after deployment
$router->get('/throw-exception', function () use ($router) {
    throw new Exception("testing");
});

/**
 * This group has authenticated routes
 */
$router->group([
    'prefix'     => 'twilio',
    'middleware' => 'auth',
], function () use ($router) {
    $router->get('/token', [
        'uses' => 'Twilio\TwilioController@obtainToken',
        'as'   => 'twilio.token',
    ]);
    $router->post('/call/js-create-conference', [
        'uses' => 'Twilio\TwilioController@jsCreateConference',
        'as'   => 'twilio.js.create.conference',
    ]);
    $router->post('/call/get-conference-info', [
        'uses' => 'Twilio\TwilioController@getConferenceInfo',
        'as'   => 'twilio.js.get.conference.info',
    ]);
    $router->post('/call/join-conference', [
        'uses' => 'Twilio\TwilioController@joinConference',
        'as'   => 'twilio.call.join.conference',
    ]);
    $router->post('/call/end', [
        'uses' => 'Twilio\TwilioController@endCall',
        'as'   => 'twilio.call.leave.conference',
    ]);
});

/**
 * This group is not authenticated,
 * because it's called directly from Twilio
 */
$router->group([
    'prefix' => 'twilio',
], function () use ($router) {
    $router->post('/call/place', [
        'uses' => 'Twilio\TwilioController@placeCall',
        'as'   => 'twilio.call.place',
    ]);
    $router->post('/call/status', [
        'uses' => 'Twilio\TwilioController@callStatusCallback',
        'as'   => 'twilio.call.status',
    ]);
    $router->post('/call/number-status', [
        'uses' => 'Twilio\TwilioController@dialNumberStatusCallback',
        'as'   => 'twilio.call.number.status',
    ]);
    $router->post('/call/dial-action', [
        'uses' => 'Twilio\TwilioController@dialActionCallback',
        'as'   => 'twilio.call.dial.action',
    ]);
    $router->post('/call/conference-status', [
        'uses' => 'Twilio\TwilioController@conferenceStatusCallback',
        'as'   => 'twilio.call.conference.status',
    ]);
    $router->post('/call/recording-status', [
        'uses' => 'Twilio\TwilioController@recordingStatusCallback',
        'as'   => 'twilio.call.recording.status',
    ]);
    $router->post('/debugger-webhook', [
        'uses' => 'Twilio\TwilioController@debuggerWebhook',
        'as'    => 'twilio.debugger.webhook',
    ]);
});

$router->get('health-check', 'HealthCheckController@isSiteUp');
