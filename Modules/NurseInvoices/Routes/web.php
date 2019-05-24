<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::prefix('nurseinvoices')->middleware(['permission:has-schedule'])->group(function () {
    Route::get('/', 'NurseInvoicesController@index');

    Route::get('invoice-review', [
        'uses' => 'InvoiceReviewController@reviewInvoice',
        'as'   => 'care.center.invoice.review',
    ]);
});
