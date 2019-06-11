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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/provider-report', 'ProviderReportController@getProviderReport');
Route::get('/home', 'HomeController@index')->name('home');

Route::get('enter-patient-form', 'InvitationLinksController@enterPatientForm')->name('enterPatientForm');
Route::post('send-invitation-link', 'InvitationLinksController@createSendInvitationUrl')->name('createSendInvitationUrl');
//this is a signed route
Route::get('login-survey/{user}/{survey}', 'InvitationLinksController@surveyLoginForm')->name('loginSurvey');
Route::post('survey-login', 'InvitationLinksController@surveyLoginAuth')->name('surveyLoginAuth');
Route::post('resend-link/{user}', 'InvitationLinksController@resendUrl')->name('resendUrl');
Route::post('save-answer', 'SurveyController@storeAnswer')->name('saveSurveyAnswer');
Route::get('get-previous-answer', 'SurveyController@getPreviousAnswer')->name('getPreviousAnswer');
Route::get('get-ppp-data/{userId}', 'PersonalizedPreventionPlanController@getPppDataForUser')->name('getPppDataForUser');
