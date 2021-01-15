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
            Route::post('/updatePracticeServices', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@updatePracticeChargeableServices',
                'as'   => 'monthly.billing.practice.services',
            ])->middleware('permission:patientSummary.read,patientSummary.update,patientSummary.create');

            Route::get('', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@index',
                'as'   => 'monthly.billing.make',
            ])->middleware('permission:patientSummary.read,patientProblem.read,chargeableService.read,practice.read');

            Route::post('/data', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@data',
                'as'   => 'monthly.billing.data',
            ])->middleware('permission:patientSummary.read,patientSummary.update,patientSummary.create');

            Route::get('/counts', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@counts',
            ])->middleware('permission:patientSummary.read');

            Route::post('/close', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@closeMonthlySummaryStatus',
                'as'   => 'monthly.billing.close.month',
            ])->middleware('permission:patientSummary.update');

            Route::post('/open', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@openMonthlySummaryStatus',
                'as'   => 'monthly.billing.open.month',
            ])->middleware('permission:patientSummary.update');

            Route::post('/updateSummaryServices', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@updateSummaryChargeableServices',
                'as'   => 'monthly.billing.updateSummaryServices',
            ])->middleware('permission:patientSummary.update');

            Route::post('/status/update', [
                'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@updateStatus',
                'as'   => 'monthly.billing.status.update',
            ])->middleware('permission:patientSummary.update');
        });
    });

    Route::group(['prefix' => 'practice/billing'], function () {
        Route::get('create', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@createInvoices',
            'as'   => 'practice.billing.create',
        ])->middleware('permission:practiceInvoice.read');

        Route::post('make', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@makeInvoices',
            'as'   => 'practice.billing.make',
        ])->middleware('permission:practiceInvoice.create');
    });

    Route::group(['prefix' => 'monthly-billing/saas'], function () {
        Route::get('', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@index',
            'as'   => 'saas-admin.monthly.billing.make',
        ]);

        Route::post('data', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\ApproveBillablePatientsController@data',
            'as'   => 'saas-admin.monthly.billing.data',
        ]);
    });

    Route::group(['prefix' => 'practice/billing'], function () {
        Route::get('create', [
            'uses' => '\CircleLinkHealth\CcmBilling\Http\Controllers\PracticeInvoiceController@createInvoices',
            'as'   => 'saas-admin.practices.billing.create',
        ]);
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
