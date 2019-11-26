<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::prefix('cpmmigrations')->group(function () {
    Route::get('/', 'CpmMigrationsController@index');
});
