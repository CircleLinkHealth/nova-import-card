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

Route::group([
    'middleware' => [
        'auth',
    ],
], function () {
    Route::get('hospitalisation-notes-dashboard', [
        'uses' => 'LivewireDatatablesController@hospitalisationNotes',
        'as'   => 'hospitalization-notes.table',
    ])->middleware(['permission:hospitalisation-notes-dashboard.view']);
    
    Route::get('home', 'HomeController@index');

    Route::get('patient/{patientId}/note/{noteId}', [
        'uses' => 'RedirectToProviderApp@notesShow',
        'as'   => 'patient.note.show',
    ]);

    Route::get('ccd-importer', [
        'uses' => 'RedirectToProviderApp@ccdImporter',
        'as'   => 'import.ccd.remix',
    ]);

    Route::get('patient/{patientId}/demographics', [
        'uses' => 'RedirectToProviderApp@showPatientDemographics',
        'as'   => 'patient.demographics.show',
    ]);

    Route::get('patient/{patientId}/notes', [
        'uses' => 'RedirectToProviderApp@notesIndex',
        'as'   => 'patient.note.index',
    ]);

    Route::get('patient/{patientId}/careplan', [
        'uses' => 'RedirectToProviderApp@showCareplan',
        'as'   => 'patient.careplan.print',
    ]);
});

Route::group([
    'middleware' => [
        'auth',
        'permission:admin-access',
    ],
], function () {
    Route::get('home', 'HomeController@index');

    Route::get('message-dispatch-messages-dashboard', [
        'uses' => 'LivewireDatatablesController@messageDispatchMessages',
        'as'   => 'message-dispatch-messages.table',
    ]);

    Route::get('call-attempt-note-dashboard', [
        'uses' => 'LivewireDatatablesController@callAttemptNote',
        'as'   => 'call-attempt-note.table',
    ]);
});
