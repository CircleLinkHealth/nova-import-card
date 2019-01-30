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
Route::get('enter-phone-number', 'AwvPatientsController@enterPhoneNumber')->name('enterPhoneNumber');
Route::get('create-send-url/{number}', 'InvitationLinksController@createSendUrl')->name('createSendUrl');
Route::get('login-survey/{patient}', 'AwvPatientsController@authenticateInvitedUser')->name('loginSurvey')/*->middleware('signed')*/;
Route::get('survey-auth', 'AwvPatientsController@authSurveyLogin')->name('surveyAuth');
Route::get('resend-link/{patient}', 'AwvPatientsController@resendUrl')->name('resendUrl');
