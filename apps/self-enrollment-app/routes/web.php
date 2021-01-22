<?php

Route::group([
    'prefix'     => 'auth',
    'middleware' => ['web'],
], function () {
    Route::get(
        '/patient-self-enrollment',
        [
            'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrollmentAuthForm',
            'as'   => 'invitation.enrollment.loginForm',
        ]
    )->middleware('signed');

    Route::get('enrollment-logout', [
        'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@logoutEnrollee',
        'as'   => 'user.enrollee.logout',
    ]);

    Route::post('login-enrollment-survey', [
        'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@authenticate',
        'as'   => 'invitation.enrollment.login',
    ]);
});
// TEMPORARY SIGNED ROUTE

Route::get('/enrollment-survey', [
    'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrollNow',
    'as'   => 'patient.self.enroll.now',
]);

Route::get('/enrollment-info', [
    'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrolleeRequestsInfo',
    'as'   => 'patient.requests.enroll.info',
]);

// Redirects to view with enrollees details to contact.
Route::get('/enrollee-contact-details', [
    'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@enrolleeContactDetails',
    'as'   => 'enrollee.to.call.details',
])->middleware('auth');

// Incoming from AWV
Route::get('/review-letter/{userId}', [
    'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@reviewLetter',
    'as'   => 'enrollee.to.review.letter',
]);


Route::get('login-enrollees-survey/{user}/{survey}', '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@sendToSurvey')
    ->name('enrollee.login.signed')
    ->middleware('signed');

Route::post('enrollee-login-viewed', [
    'uses' => '\CircleLinkHealth\Eligibility\SelfEnrollment\Http\Controllers\SelfEnrollmentController@viewFormVisited',
    'as'   => 'enrollee.login.viewed',
])->middleware('guest');
