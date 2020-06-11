<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::prefix('eligibility')->group(function () {
    Route::post('process-eligibility/drive/', [
        'uses' => 'ProcessEligibilityController@fromGoogleDrive',
        'as'   => 'process.eligibility.google.drive',
    ])->middleware(['auth', 'role:administrator']);

    Route::group([
        'middleware' => [
            'auth',
            'permission:admin-access',
        ],
        'prefix' => 'admin',
    ], function () {
        Route::get(
            'eligible-lists/phoenix-heart',
            'WelcomeCallListController@makePhoenixHeartCallList'
        )->middleware('permission:batch.create');

        Route::post('make-welcome-call-list', [
            'uses' => 'WelcomeCallListController@makeWelcomeCallList',
            'as'   => 'make.welcome.call.list',
        ])->middleware('permission:batch.create');

        Route::group(['prefix' => 'batches'], function () {
            Route::get('pending-jobs/count', [
                'uses' => 'EligibilityBatchController@allJobsCount',
                'as'   => 'all.eligibility.jobs.count',
            ])->middleware('permission:batch.read');

            Route::get('', [
                'uses' => 'EligibilityBatchController@index',
                'as'   => 'eligibility.batches.index',
            ])->middleware('permission:batch.read');

            Route::get('google-drive/create', [
                'uses' => 'EligibilityBatchController@googleDriveCreate',
                'as'   => 'eligibility.batches.google.drive.create',
            ]);

            Route::get('csv/create', [
                'uses' => 'EligibilityBatchController@csvCreate',
                'as'   => 'eligibility.batches.csv.create',
            ]);

            Route::group(['prefix' => '{batch}'], function () {
                Route::get('', [
                    'uses' => 'EligibilityBatchController@show',
                    'as'   => 'eligibility.batch.show',
                ])->middleware('permission:batch.read,practice.read,ccda.read');

                Route::get('/counts', [
                    'uses' => 'EligibilityBatchController@getCounts',
                    'as'   => 'eligibility.batch.getCounts',
                ])->middleware('permission:enrollee.read,ccda.read');

                Route::get('/eligible-csv', [
                    'uses' => 'EligibilityBatchController@downloadEligibleCsv',
                    'as'   => 'eligibility.download.csv.eligible',
                ])->middleware('permission:enrollee.read');

                Route::get('/entire-patient-list-csv', [
                    'uses' => 'EligibilityBatchController@downloadAllPatientsCsv',
                    'as'   => 'eligibility.download.all',
                ])->middleware('permission:enrollee.read');

                Route::get('supplemental-insurance-info-csv', [
                    'uses' => 'EligibilityBatchController@downloadAthenaApiInsuranceInfoCsv',
                    'as'   => 'eligibility.download.supplemental_insurance_info',
                ])->middleware('permission:enrollee.read');

                Route::get('/reprocess', [
                    'uses' => 'EligibilityBatchController@getReprocess',
                    'as'   => 'get.eligibility.reprocess',
                ])->middleware('permission:enrollee.read');

                Route::post('/reprocess', [
                    'uses' => 'EligibilityBatchController@postReprocess',
                    'as'   => 'post.eligibility.reprocess',
                ])->middleware('permission:enrollee.read');

                Route::get('/last-import-session-logs', [
                    'uses' => 'EligibilityBatchController@getLastImportLog',
                    'as'   => 'eligibility.download.last.import.logs',
                ])->middleware('permission:batch.read');

                Route::get('/download-patient-list-csv', [
                    'uses' => 'EligibilityBatchController@downloadCsvPatientList',
                    'as'   => 'eligibility.download.csv.patient.list',
                ])->middleware('permission:batch.read');

                Route::get('/batch-logs-csv', [
                    'uses' => 'EligibilityBatchController@downloadBatchLogCsv',
                    'as'   => 'eligibility.download.logs.csv',
                ])->middleware('permission:batch.read');
            });
        });

        Route::post('commonwealth-pcm', [
            'uses' => 'CommonwealthPCMController@downloadCsvList',
            'as'   => 'commonwealth.pcm.alpha.version',
        ])->middleware('role:administrator');

        Route::group(['prefix' => 'enrollees'], function () {
            Route::get('', [
                'uses' => 'EnrolleesController@index',
                'as'   => 'admin.enrollees.index',
            ])->middleware('permission:enrollee.read,practice.read');
            Route::get('batch/{batch}', [
                'uses' => 'EnrolleesController@showBatch',
                'as'   => 'admin.enrollees.show.batch',
            ])->middleware('permission:enrollee.read,practice.read,batch.read');
            Route::post('{batch}/import', [
                'uses' => 'EnrolleesController@import',
                'as'   => 'admin.enrollees.import',
            ])->middleware('permission:enrollee.read,enrollee.update');
            Route::post('import', [
                'uses' => 'EnrolleesController@import',
                'as'   => 'admin.enrollees.import.from.all.practices',
            ])->middleware('permission:enrollee.read,enrollee.update');
            Route::post('/import-array-of-ids', [
                'uses' => 'EnrolleesController@importArray',
                'as'   => 'admin.enrollees.import.array',
            ])->middleware('permission:enrollee.read,enrollee.update');
            Route::post('/import-using-medical-record-id', [
                'uses' => 'EnrolleesController@importMedicalRecords',
                'as'   => 'admin.enrollees.import.medical.records',
            ])->middleware('permission:ccd-import');
        });
    });
});
