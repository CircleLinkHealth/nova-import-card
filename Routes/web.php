<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::prefix('eligibility')->group(function () {
    Route::post('process-eligibility/drive/', [
        'uses' => 'ProcessEligibilityController@fromGoogleDrive',
        'as'   => 'process.eligibility.google.drive',
    ])->middleware(['auth', 'role:administrator']);
    
    Route::get(
        'process-eligibility/local-zip-from-drive/{dir}/{practiceName}/{filterLastEncounter}/{filterInsurance}/{filterProblems}',
        [
            'uses' => 'ProcessEligibilityController@fromGoogleDriveDownloadedLocally',
            'as'   => 'process.eligibility.local.zip',
        ]
    )->middleware(['auth', 'role:administrator']);
    
    
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
    });
    
});

Route::group([
                 'middleware' => [
                     'auth',
                     'permission:ccd-import',
                 ],
                 'prefix' => 'ccd-importer',
             ], function () {
    Route::post('demographics', 'DemographicsImportsController@store');
});
