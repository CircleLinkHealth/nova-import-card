<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::prefix('nurseinvoices')->group(function () {
    Route::get('/', 'NurseInvoicesController@index');
});
