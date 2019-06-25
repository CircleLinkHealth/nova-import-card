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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')
     ->name('home');

Route::get('enter-patient-form', 'InvitationLinksController@enterPatientForm')
     ->name('enterPatientForm');

Route::post('send-invitation-link', 'InvitationLinksController@createSendInvitationUrl')
     ->name('createSendInvitationUrl');

//this is a signed route
Route::get('login-survey/{user}/{survey}', 'InvitationLinksController@surveyLoginForm')
     ->name('loginSurvey');

Route::post('survey-login', 'InvitationLinksController@surveyLoginAuth')
     ->name('surveyLoginAuth');

Route::post('resend-link/{user}', 'InvitationLinksController@resendUrl')
     ->name('resendUrl');

//don't like this, why is it here?
//Route::get('get-previous-answer', 'SurveyController@getPreviousAnswer')
//     ->name('getPreviousAnswer');

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
    //fixme: add reports routes here
    Route::get('/provider-report/{userId}', 'ProviderReportController@getProviderReport')->name('provider-report');

    Route::get('get-ppp-data/{userId}', 'PersonalizedPreventionPlanController@getPppDataForUser')
         ->name('getPppDataForUser');
});
