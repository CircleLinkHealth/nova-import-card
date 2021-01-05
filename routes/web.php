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

Route::get('/', 'HomeController@index')->name('home');
Route::get('home', 'HomeController@index');

Route::get('ccd-importer', [
    'uses' => 'RedirectToProviderApp@ccdImporter',
    'as'   => 'import.ccd.remix',
]);

Route::get('patient/{patientId}/demographics', [
    'uses' => 'RedirectToProviderApp@showPatientDemographics',
    'as'   => 'patient.demographics.show',
]);