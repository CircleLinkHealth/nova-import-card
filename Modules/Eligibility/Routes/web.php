<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::prefix('eligibility')->group(function () {
    Route::get('/', 'EligibilityController@index');
});
