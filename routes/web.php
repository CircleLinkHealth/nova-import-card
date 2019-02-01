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
Route::get('enter-phone-number', 'InvitationLinksController@enterPhoneNumber')->name('enterPhoneNumber');
Route::get('login-survey/{patient}', 'InvitationLinksController@surveyFormAuth')->name('loginSurvey');
Route::post('survey-auth/{patient}', 'InvitationLinksController@authSurveyLogin')->name('surveyAuth');
Route::get('resend-link/{patient}', 'InvitationLinksController@resendUrl')->name('resendUrl');
