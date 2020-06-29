<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::prefix('nurseinvoices')->middleware(['auth'])->group(function () {
    Route::middleware(['permission:has-schedule'])->group(function () {
        Route::get('review', [
            'uses' => 'InvoiceReviewController@reviewInvoice',
            'as'   => 'care.center.invoice.review',
        ]);

        Route::post('dispute', [
            'uses' => 'InvoiceReviewController@disputeInvoice',
            'as'   => 'care.center.invoice.dispute',
        ]);

        Route::post('daily-dispute', [
            'uses' => 'NurseInvoiceDailyDisputesController@storeSuggestedWorkTime',
            'as'   => 'care.center.invoice.daily.dispute',
        ]);

        Route::delete('delete-dispute/{invoiceId}/{disputedDay}', [
            'uses' => 'NurseInvoiceDailyDisputesController@deleteSuggestedWorkTime',
            'as'   => 'care.center.delete.invoice.daily.dispute',
        ]);

        Route::post('approve', [
            'uses' => 'InvoiceReviewController@approveInvoice',
            'as'   => 'care.center.invoice.approve',
        ]);

        Route::post('invoice', [
            'uses' => 'InvoiceReviewController@show',
            'as'   => 'nurseinvoices.show',
        ]);
    });

    Route::get('nurse/{nurse_user_id}/invoice/{invoice_id?}', [
        'uses' => 'InvoiceReviewController@adminShow',
        'as'   => 'nurseinvoices.admin.show',
    ]);

    Route::group([
        'middleware' => [
            'auth',
            'permission:admin-access',
        ],
        'prefix' => 'admin/download',
    ], function () {
        Route::get('invoices', [
            'uses' => 'InvoicesDownload\TestDownloadInvoice@collectInvoicesFor',
            'as'   => 'collect.nurses.invoices',
        ])->middleware('auth');
    });
});
