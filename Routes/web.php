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

Route::group(['middleware' => 'auth'], function () {

    // **** PATIENTS (/manage-patients/{patientId}/)
    Route::group([
        'prefix'     => 'manage-patients/{patientId}',
        'middleware' => ['patientProgramSecurity', 'checkWebSocketServer'],
    ], function () {

        Route::group(['prefix' => 'activities'], function () {
            Route::get('create', [
                'uses' => 'ActivityController@create',
                'as'   => 'patient.activity.create',
            ])->middleware('permission:patient.read,offlineActivity.create');
            Route::post('store', [
                'uses' => 'ActivityController@store',
                'as'   => 'patient.activity.store',
            ])->middleware('permission:activity.create,offlineActivity.create,patientSummary.create,patientSummary.update');
            Route::get('view/{actId}', [
                'uses' => 'ActivityController@show',
                'as'   => 'patient.activity.view',
            ])->middleware('permission:activity.read,patient.read,provider.read');
            Route::get('', [
                'uses' => 'ActivityController@providerUIIndex',
                'as'   => 'patient.activity.providerUIIndex',
            ])->middleware('permission:activity.read,patient.read,provider.read');

            Route::get('getCurrent', [
                'uses' => 'ActivityController@getCurrentForPatient',
                'as'   => 'patient.activity.get.current.for.patient',
            ])->middleware('permission:activity.read,patient.read,provider.read');
        });

    });


});