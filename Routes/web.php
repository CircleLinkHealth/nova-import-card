<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::group([
    'middleware' => ['web', 'auth'],
], function () {
    Route::group([
        'prefix' => 'reports',
    ], function () {
        Route::group([
            'prefix' => 'monthly-billing',
        ], function () {
            Route::get('', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@index',
                'as'   => 'monthly.billing.make',
            ]);

            Route::post('/data', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@data',
                'as'   => 'monthly.billing.data',
            ]);

            Route::get('/counts', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@counts',
            ]);

            Route::post('/successful-calls-count', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@successfulCallsCount',
            ]);

            Route::post('/set-billing-status', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@setBillingStatus',
                'as'   => 'monthly.billing.set.status',
            ]);

            Route::post('/close', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@closeMonth',
                'as'   => 'monthly.billing.close.month',
            ]);

            Route::post('/open', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@openMonth',
                'as'   => 'monthly.billing.open.month',
            ]);

            Route::post('/set-practice-services', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@setPracticeChargeableServices',
                'as'   => 'monthly.billing.set.practice.services',
            ]);

            Route::post('/set-patient-services', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@setPatientChargeableServices',
                'as'   => 'monthly.billing.set.patient.services',
            ]);

            Route::post('/set-billing-status', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@setBillingStatus',
                'as'   => 'monthly.billing.set.status',
            ]);
        });
    });

    Route::group(['prefix' => 'practice/billing'], function () {
        Route::get('create', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@createInvoices',
            'as'   => 'practices.billing.create',
        ])->middleware('permission:practiceInvoice.read');

        Route::post('make', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@makeInvoices',
            'as'   => 'practices.billing.make',
        ])->middleware('permission:practiceInvoice.create');
    });
});

//This route was replaced by route with url '/downloadInvoice/{practice}/{name}', and name 'monthly.billing.download'.
//We keep it here to support Report links mailed before 5/12/17.
Route::get('/admin/reports/monthly-billing/v2/downloadInvoice/{practice}/{name}', [
    'uses'       => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@downloadInvoice',
    'middleware' => ['auth'],
]);

Route::get('/downloadInvoice/{practice}/{name}', [
    'uses'       => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@downloadInvoice',
    'as'         => 'monthly.billing.download',
    'middleware' => ['auth'],
]);
