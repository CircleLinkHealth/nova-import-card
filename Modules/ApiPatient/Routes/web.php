<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::group(['prefix' => 'api'], function () {
    Route::group([
        'prefix'     => 'patients',
        'middleware' => ['patientProgramSecurity'],
    ], function () {
        Route::group(
            [
                'prefix' => '{userId}',
            ],
            function () {
                Route::prefix('problems')->group(
                    function () {
                        Route::get('ccd', 'CcdProblemController@show')->middleware('permission:patientProblem.read');
                        Route::post(
                            'ccd',
                            'CcdProblemController@store'
                        )->middleware('permission:patientProblem.create');
                        Route::put(
                            'ccd/{ccdProblemId}',
                            'CcdProblemController@update'
                        )->middleware('permission:patientProblem.update');
                        Route::delete(
                            'ccd/{ccdProblemId}',
                            'CcdProblemController@destroy'
                        )->middleware('permission:patientProblem.delete');
                    }
                );
            }
        );
    });
});
