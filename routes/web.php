<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')
     ->name('home');

Route::get('enter-patient-form', 'InvitationLinksController@enterPatientForm')
     ->name('enter.patient.form');

Route::group([
    'prefix' => 'auth',
], function () {

    Route::post('send-invitation-link', 'InvitationLinksController@createSendInvitationUrl')
         ->name('auth.send.signed.url');

    //this is a signed route
    Route::get('login-survey/{user}/{survey}', 'Auth\PatientLoginController@showLoginForm')
         ->name('auth.login.signed')
         ->middleware('signed');

    Route::post('login-survey', 'Auth\PatientLoginController@login')
         ->name('auth.login.with.signed');

});

Route::group([
    'prefix'     => 'manage-patients',
    'middleware' => ['auth'],
], function () {

    Route::get('', [
        'uses' => 'PatientController@index',
        'as'   => 'patient.list',
    ]);

    Route::get('list', [
        'uses' => 'PatientController@getPatientList',
        'as'   => 'patient.list.ajax',
    ]);
});

Route::group([
    'prefix'     => 'survey',
    'middleware' => ['auth'],
], function () {

    Route::group([
        'prefix' => 'hra',
    ], function () {

        Route::get('{practiceId}/{patientId}/{surveyId}', [
            'uses' => 'SurveyController@getSurvey',
            'as'   => 'survey.hra',
        ]);

        //why are we passing practice id here?
        Route::post('{practiceId}/{patientId}/save-answer', [
            'uses' => 'SurveyController@storeAnswer',
            'as'   => 'survey.hra.store.answer',
        ]);

    });

    Route::group([
        'prefix' => 'vitals',
    ], function () {

        Route::get('{practiceId}/{patientId}/welcome', [
            'uses' => 'VitalsSurveyController@showWelcome',
            'as'   => 'survey.vitals.welcome',
        ]);

        Route::get('{practiceId}/{patientId}/not-auth', [
            'uses' => 'VitalsSurveyController@showNotAuthorized',
            'as'   => 'survey.vitals.not.authorized',
        ]);

        Route::get('{practiceId}/{patientId}', [
            'uses' => 'VitalsSurveyController@getSurvey',
            'as'   => 'survey.vitals',
        ]);

        Route::post('{practiceId}/{patientId}/save-answer', [
            'uses' => 'VitalsSurveyController@storeAnswer',
            'as'   => 'survey.vitals.store.answer',
        ]);

    });
});

Route::group([
    'prefix'     => 'reports',
    'middleware' => ['auth'],
], function () {

    Route::get('/provider-report/{userId}', 'ProviderReportController@getProviderReport')->name('provider-report');

    Route::get('get-ppp-data/{userId}', 'PersonalizedPreventionPlanController@getPppDataForUser')
         ->name('getPppDataForUser');
});

Route::post('twilio/sms/status', 'TwilioController@smsStatusCallback')
    ->name('twilio.sms.status');
