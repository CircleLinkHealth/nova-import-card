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
    'uses' => 'RedirectToOtherApp@ccdImporter',
    'as'   => 'import.ccd.remix',
]);

Route::get('pinfo', [
    'uses' => 'RedirectToOtherApp@pinfo',
])->middleware(['auth', 'role:administrator']);

    Route::get('patient/{patientId}/notes', [
        'uses' => 'RedirectToProviderApp@notesIndex',
        'as'   => 'patient.note.index',
    ]);

    Route::get('patient/{patientId}/careplan', [
        'uses' => 'RedirectToProviderApp@showCareplan',
        'as'   => 'patient.careplan.print',
    ]);
});
Route::get('config', [
    'uses' => 'RedirectToOtherApp@config',
])->middleware(['auth', 'role:administrator']);
