<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::group([
    'prefix'     => 'auth',
    'middleware' => ['web'],
], function () {
    Route::get(
        '/patient-self-enrollment',
        [
            'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrollmentAuthForm',
            'as'   => 'invitation.enrollment.loginForm',
        ]
    )->middleware('signed');

    Route::get('enrollment-logout', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController@logoutEnrollee',
        'as'   => 'user.enrollee.logout',
    ]);

    Route::post('login-enrollment-survey', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController@authenticate',
        'as'   => 'invitation.enrollment.login',
    ]);
});
// TEMPORARY SIGNED ROUTE

Route::get('/enrollment-survey', [
    'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrollNow',
    'as'   => 'patient.self.enroll.now',
]);

Route::get('/enrollment-info', [
    'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrolleeRequestsInfo',
    'as'   => 'patient.requests.enroll.info',
]);

// Redirects to view with enrollees details to contact.
Route::get('/enrollee-contact-details', [
    'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrolleeContactDetails',
    'as'   => 'enrollee.to.call.details',
])->middleware('auth');

// Incoming from AWV
Route::get('/review-letter/{userId}', [
    'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController@reviewLetter',
    'as'   => 'enrollee.to.review.letter',
]);

Route::get('login-enrollees-survey/{user}/{survey}', '\CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController@sendToSurvey')
    ->name('enrollee.login.signed')
    ->middleware('signed');

Route::post('enrollee-login-viewed', [
    'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController@viewFormVisited',
    'as'   => 'enrollee.login.viewed',
])->middleware('guest');

Route::group([
    'prefix'     => 'admin',
    'middleware' => [
        'auth',
        'permission:admin-access',
    ],
], function () {
    Route::get('/send-enrollee-reminder-test', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\AutoEnrollmentTestDashboard@sendEnrolleesReminderTestMethod',
        'as'   => 'send.reminder.enrollee.qa',
    ])->middleware('auth');

    Route::get('/send-patient-reminder-test', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\AutoEnrollmentTestDashboard@sendPatientsReminderTestMethod',
        'as'   => 'send.reminder.patient.qa',
    ])->middleware('auth');

    Route::get('/final-action-unreachables-test', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\AutoEnrollmentTestDashboard@finalActionTest',
        'as'   => 'final.action.qa',
    ])->middleware('auth');

    Route::get('/evaluate-enrolled-from-survey', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\AutoEnrollmentTestDashboard@evaluateEnrolledForSurveyTest',
        'as'   => 'evaluate.survey.completed',
    ])->middleware('auth');

    Route::get('/reset-enrollment-test', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\AutoEnrollmentTestDashboard@resetEnrollmentTest',
        'as'   => 'reset.test.qa',
    ])->middleware('auth');

    Route::get('/send-enrollee-invites', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\AutoEnrollmentTestDashboard@inviteEnrolleesToEnrollTest',
        'as'   => 'send.enrollee.invitations',
    ])->middleware('auth');

    Route::get('/send-unreachable-invites', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\AutoEnrollmentTestDashboard@inviteUnreachablesToEnrollTest',
        'as'   => 'send.unreachable.invitations',
    ])->middleware('auth');

    Route::get('/trigger-enrolldata-test', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\AutoEnrollmentTestDashboard@triggerEnrollmentSeederTest',
        'as'   => 'trigger.enrolldata.test',
    ])->middleware('auth');

    Route::get('/invite-unreachable', [
        'uses' => '\CircleLinkHealth\SelfEnrollment\Http\Controllers\AutoEnrollmentTestDashboard@sendInvitesPanelTest',
        'as'   => 'send.invitations.panel',
    ])->middleware('auth');
    //---------------------------------------
});
