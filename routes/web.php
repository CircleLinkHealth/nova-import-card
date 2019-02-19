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

Route::group([
    'middleware' => [
        'auth',
    ],
    'prefix' => 'survey',
], function () {
    Route::post('answer', [
        'uses' => 'SurveyController@storeAnswer',
        'as'   => 'survey.answer-store',
    ]);
});
