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

Route::get('/provider-report', 'ProviderReportController@getProviderReport');

Route::get('enter-patient-form', 'InvitationLinksController@enterPatientForm')
     ->name('enterPatientForm');

Route::post('send-invitation-link', 'InvitationLinksController@createSendInvitationUrl')
     ->name('createSendInvitationUrl');

//this is a signed route
Route::get('login-survey/{user}/{survey}', 'InvitationLinksController@surveyLoginForm')
     ->name('loginSurvey');

//fixme: thoughts: the surveys should be accessible from a url, so the POST here should redirect to a route, and not return a view
Route::post('survey-login', 'InvitationLinksController@surveyLoginAuth')
     ->name('surveyLoginAuth');

Route::post('resend-link/{user}', 'InvitationLinksController@resendUrl')
     ->name('resendUrl');

Route::post('save-answer', 'SurveyController@storeAnswer')
     ->name('saveSurveyAnswer');

Route::get('get-previous-answer', 'SurveyController@getPreviousAnswer')
     ->name('getPreviousAnswer');

Route::get('get-ppp-data', 'PersonalizedPreventionPlanController@getPppDataForUser')
     ->name('getPppDataForUser');

Route::group([
    'prefix' => 'survey',
    //    'middleware' => ['auth'],
], function () {

    Route::group([
        'prefix' => 'hra',
    ], function () {
        //fixme: add HRA routes here
    });

    Route::group([
        'prefix' => 'vitals',
    ], function () {

        Route::get('{practiceId}/{patientId}/welcome', [
            'uses' => 'VitalsSurveyController@showWelcome',
            'as' => 'survey.vitals.welcome',
        ]);

        Route::get('{practiceId}/{patientId}/not-auth', [
            'uses' => 'VitalsSurveyController@showNotAuthorized',
            'as' => 'survey.vitals.not.authorized',
        ]);

        Route::get('{practiceId}/{patientId}', [
            'uses' => 'VitalsSurveyController@getSurvey',
            'as' => 'survey.vitals',
        ]);

        Route::post('store-answer', [
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
});
