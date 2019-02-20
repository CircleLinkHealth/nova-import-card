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

use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('enter-patient-form', 'InvitationLinksController@enterPatientForm')->name('enterPatientForm');
Route::post('send-invitation-link', 'InvitationLinksController@createSendInvitationUrl')->name('createSendInvitationUrl');
//this is a signed route
Route::get('login-survey/{user}/{survey}', 'InvitationLinksController@surveyLoginForm')->name('loginSurvey');
Route::post('survey-login', 'InvitationLinksController@surveyAuthBeforeRedirect')->name('surveyLoginForm');//@todo:rename
Route::post('resend-link/{user}', 'InvitationLinksController@resendUrl')->name('resendUrl');
Route::post('save-survey-answer', 'SurveyController@saveAnswer')->name('saveSurveyAnswer');
