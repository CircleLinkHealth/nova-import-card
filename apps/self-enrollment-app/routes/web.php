<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'HomeController@showHomepage')->name('home');

Route::group([
    'middleware' => ['auth'],
], function () {
    Route::get('/home', 'HomeController@showHomepage');
});

