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


use App\Http\Controllers\InvitationLinksController;
use App\InvitationLink;

Route::get('/', function () {
    return view('welcome');
});
Route::get('enter-phone-number', 'AwvPatientsController@enterPhoneNumber')->name('enter-phone-number');
Route::get('create-url/{patient}', 'AwvPatientsController@createUrl')->name('create-url');
Route::get('login-survey/{patient}', 'AwvPatientsController@authenticateInvitedUser')->name('login-survey')->middleware('signed');

Route::get('survey-auth', 'AwvPatientsController@authSurveyLogin')->name('survey-auth');