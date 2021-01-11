<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('/', 'HomeController@index')
    ->name('home');

Auth::routes();

Route::post('/logout', [
    'uses' => 'Auth\LoginController@logout',
    'as'   => 'logout',
])->middleware('auth');

Route::group([
    'prefix' => 'auth',
], function () {
    //this is a signed route
    Route::get('login-survey/{user}/{survey}', 'Auth\PatientLoginController@showLoginForm')
        ->name('auth.login.signed')
        ->middleware('signed');

    Route::post('login-survey', 'Auth\PatientLoginController@login')
        ->name('auth.login.with.signed');
});

Route::group([
    'prefix'     => 'manage-patients',
    'middleware' => ['auth', 'permission:vitals-survey-complete'],
], function () {
    Route::get('', [
        'uses' => 'PatientController@index',
        'as'   => 'patient.list',
    ]);

    Route::post('store', [
        'uses' => 'PatientController@store',
        'as'   => 'patient.store',
    ]);

    Route::get('list', [
        'uses' => 'PatientController@getPatientList',
        'as'   => 'patient.list.ajax',
    ]);

    Route::get('{userId}/contact-info', [
        'uses' => 'PatientController@getPatientContactInfo',
        'as'   => 'patient.contact.info',
    ]);

    Route::post('{userId}/send-link/hra', [
        'uses' => 'InvitationLinksController@sendHraLink',
        'as'   => 'patient.send.link.hra',
    ]);

    Route::post('{userId}/send-link/vitals', [
        'uses' => 'InvitationLinksController@sendVitalsLink',
        'as'   => 'patient.send.link.vitals',
    ]);

    Route::get('{userId}/enroll', [
        'uses' => 'InvitationLinksController@showEnrollUserForm',
        'as'   => 'patient.enroll.show',
    ]);

    Route::post('{userId}/enroll', [
        'uses' => 'InvitationLinksController@enrollUser',
        'as'   => 'patient.enroll',
    ]);

    Route::get(
        '{userId}/{surveyName}/{channel}/send-assessment-link',
        [
            'uses' => 'InvitationLinksController@showSendAssessmentLinkForm',
            'as'   => 'patient.assessment-link-form',
        ]
    );

    Route::group([
        'prefix' => 'providers',
    ], function () {
        Route::post('add', [
            'uses' => 'ProviderController@add',
            'as'   => 'provider.add',
        ]);

        Route::get('search', [
            'uses' => 'ProviderController@search',
            'as'   => 'provider.search',
        ]);
    });

    Route::group([
        'prefix' => 'practices',
    ], function () {
        Route::get('search', [
            'uses' => 'PracticeController@search',
            'as'   => 'practice.search',
        ]);
    });
});

Route::group([
    'prefix'     => 'survey',
    'middleware' => ['auth'],
], function () {
    Route::group([
        'prefix' => 'enrollees',
    ], function () {
        Route::get('{patientId}/{surveyId}', [
            'uses' => 'EnrolleeSurveyController@getSurvey',
            'as'   => 'survey.enrollees',
        ]);

        Route::post('{patientId}/save-answer', [
            'uses' => 'EnrolleeSurveyController@storeAnswer',
            'as'   => 'survey.enrollees.store.answer',
        ]);

        Route::get('logout-successful', [
            'uses' => 'EnrolleeSurveyController@showLogoutSuccessful',
            'as'   => 'enrollee.show.logout.success',
        ]);
    });

    Route::group([
        'prefix' => 'hra',
    ], function () {
        Route::get('{patientId}', [
            'uses' => 'SurveyController@getCurrentSurvey',
            'as'   => 'survey.hra.current',
        ]);

        Route::get('{patientId}/{surveyId}', [
            'uses' => 'SurveyController@getSurvey',
            'as'   => 'survey.hra',
        ]);

        Route::post('{patientId}/save-answer', [
            'uses' => 'SurveyController@storeAnswer',
            'as'   => 'survey.hra.store.answer',
        ]);
    });

    Route::group([
        'prefix'     => 'vitals',
        'middleware' => ['auth', 'permission:vitals-survey-complete'],
    ], function () {
        Route::get('{patientId}', [
            'uses' => 'VitalsSurveyController@getCurrentSurvey',
            'as'   => 'survey.vitals',
        ]);

        Route::post('{patientId}/save-answer', [
            'uses' => 'VitalsSurveyController@storeAnswer',
            'as'   => 'survey.vitals.store.answer',
        ]);
    });

    Route::group([
        'prefix' => 'vitals',
    ], function () {
        Route::get('{patientId}/welcome', [
            'uses' => 'VitalsSurveyController@showWelcome',
            'as'   => 'survey.vitals.welcome',
        ]);

        Route::get('{patientId}/not-auth', [
            'uses' => 'VitalsSurveyController@showNotAuthorized',
            'as'   => 'survey.vitals.not.authorized',
        ]);
    });
});

Route::group([
    'prefix'     => 'reports',
    'middleware' => ['auth'],
], function () {
    Route::get('get-patient-report/{userId}/{reportType}/{year}', [
        'uses' => 'PatientController@getPatientReport',
        'as'   => 'patient.get-report',
    ]);

    Route::get('/provider-report/{userId}/{year?}', 'ProviderReportController@getProviderReport')
        ->name('get-provider-report');

    Route::get('get-ppp-data/{userId}/{year?}', 'PersonalizedPreventionPlanController@getPppForUser')
        ->name('get-ppp-report');
});

Route::group([
    'prefix' => 'survey',
], function () {
    Route::group([
        'prefix' => 'enrollees',
    ], function () {
        Route::get('create-url/{userId}/{surveyId}', [
            'uses' => 'EnrolleeSurveyController@createEnrolleesSurveyUrl',
            'as'   => 'create.enrollees.survey.url',
        ]);

        Route::post('get-enrollable-data', [
            'uses' => 'EnrolleeSurveyController@getEnrollableQuestionsData',
            'as'   => 'get.enrollable.data',
        ]);
    });
});

Route::post('twilio/sms/status', 'TwilioController@smsStatusCallback')
    ->name('twilio.sms.status');
