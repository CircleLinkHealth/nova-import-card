<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::group(['prefix' => 'api'], function () {
    Route::group([
        'prefix'     => 'patients',
        'middleware' => ['patientProgramSecurity'],
    ], function () {
        Route::get('', [
            'uses' => 'PatientController@index',
            'as'   => 'get.patientlist.index',
        ])->middleware('permission:patient.read');

        Route::group(
            [
                'prefix' => '{userId}',
            ],
            function () {
                Route::get('', 'PatientController@show')->middleware('permission:patient.read');

                Route::prefix('problems')->group(
                    function () {
                        Route::get('ccd', 'CcdProblemController@show')->middleware('permission:patientProblem.read');
                        Route::post('ccd', 'CcdProblemController@store')->middleware('permission:patientProblem.create');
                        Route::put('ccd/{ccdProblemId}', 'CcdProblemController@update')->middleware('permission:patientProblem.update');
                        Route::delete('ccd/{ccdProblemId}', 'CcdProblemController@destroy')->middleware('permission:patientProblem.delete');

                        Route::post('attest-summary-problems', 'AttestedConditionsController@update')
                            ->middleware('permission:attestedProblems.update,attestedProblems.delete');
                        Route::get('unique-to-attest', 'AttestedConditionsController@getUniqueConditionsToAttest')
                            ->middleware('permission:patientProblem.read');
                    }
                );

                Route::prefix('allergies')->group(
                    function () {
                        Route::get('', 'AllergyController@index')->middleware('permission:allergy.read');
                        Route::post('', 'AllergyController@store')->middleware('permission:allergy.create');
                        Route::delete('{allergyId}', 'AllergyController@destroy')->middleware('permission:allergy.delete');
                    }
                );

                Route::prefix('misc')->group(
                    function () {
                        Route::get('', 'UserMiscController@getMisc')->middleware('permission:misc.read');
                        Route::get('{miscTypeId}', 'UserMiscController@getMiscByType')->middleware('permission:misc.read');
                        Route::post('', 'UserMiscController@addMisc')->middleware('permission:misc.create');
                        Route::post('{miscId}/instructions', 'UserMiscController@addInstructionToMisc')->middleware('permission:misc.create,misc.delete');
                        Route::delete('{miscId}/instructions/{instructionId}', 'UserMiscController@removeInstructionFromMisc')->middleware('permission:misc.delete');
                        Route::delete('{miscId}', 'UserMiscController@removeMisc')->middleware('permission:misc.delete');
                    }
                );

                Route::prefix('appointments')->group(
                    function () {
                        Route::get('', 'AppointmentController@show')->middleware('permission:appointment.read');
                        Route::post('', 'AppointmentController@store')->middleware('permission:appointment.create');
                        Route::delete('{appointmentId}', 'AppointmentController@destroy')->middleware('permission:appointment.delete');
                    }
                );

                Route::prefix('biometrics')->group(function () {
                    Route::get('', 'UserBiometricController@show')->middleware('permission:biometric.read');
                    Route::post('', 'UserBiometricController@store')->middleware('permission:biometric.create');
                    Route::delete('{id}', 'UserBiometricController@destroy')->middleware('permission:biometric.delete');
                });

                Route::group([
                    'prefix' => 'problems',
                ], function () {
                    Route::get('', 'UserCpmProblemController@index@getProblems')->middleware('permission:patientProblem.read');
                    Route::post('', 'UserCpmProblemController@addCpmProblem')->middleware('permission:patientProblem.create,patientProblem.update');
                    Route::get('cpm', 'UserCpmProblemController@getCpmProblems')->middleware('permission:patientProblem.read');
                    Route::delete('cpm/{cpmId}', 'UserCpmProblemController@removeCpmProblem')->middleware('permission:instruction.delete,patientProblem.delete');
                });

                Route::group([
                    'prefix' => 'symptoms',
                ], function () {
                    Route::get('', 'SymptomsController@show')->middleware('permission:symptom.read');
                    Route::post('', 'SymptomsController@store')->middleware('permission:symptom.create');
                    Route::delete('{symptomId}', 'SymptomsController@destroy')->middleware('permission:symptom.delete');
                });

                Route::group([
                    'prefix' => 'medication',
                ], function () {
                    Route::get('', 'MedicationController@getMedication')->middleware('permission:medication.read');
                    Route::post('', 'MedicationController@addMedication')->middleware('permission:medication.create');
                    Route::put('{id}', 'MedicationController@editMedication')->middleware('permission:medication.update');
                    Route::delete('{medicationId}', 'MedicationController@removeMedication')->middleware('permission:medication.delete');
                    Route::get('groups', 'MedicationController@getMedicationGroups')->middleware('permission:medication.read');
                });

                Route::group([
                    'prefix' => 'providers',
                ], function () {
                    Route::get('', 'ProviderInfoController@show')->middleware('permission:provider.read');
                });

                Route::get('lifestyles', 'UserLifestyleController@getLifestyles')->middleware('permission:lifestyle.read');
                Route::post('lifestyles', 'UserLifestyleController@addLifestyle')->middleware('permission:lifestyle.create');
                Route::delete('lifestyles/{lifestyleId}', 'UserLifestyleController@removeLifestyle')->middleware('permission:lifestyle.delete');

                Route::get('notes', 'NoteController@show')->middleware('permission:note.read');
                Route::post('notes', 'NoteController@store')->middleware('permission:note.create');
                Route::put('notes/{id}', 'NoteController@update')->middleware('permission:note.update');
            }
        );
    });
});
