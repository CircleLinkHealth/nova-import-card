<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::prefix('nurseinvoices')->middleware(['permission:has-schedule'])->group(function () {
    Route::get('/', 'NurseInvoicesController@index');

    Route::get('review', [
        'uses' => 'InvoiceReviewController@reviewInvoice',
        'as'   => 'care.center.invoice.review',
    ]);

    Route::post('dispute', [
        'uses' => 'InvoiceReviewController@disputeInvoice',
        'as'   => 'care.center.invoice.dispute',
    ]);
});
