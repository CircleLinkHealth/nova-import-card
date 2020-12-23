<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('health-check', 'HealthCheckController@isSiteUp');

Route::get('/apm', '\Done\LaravelAPM\ApmController@index')->name('apm')->middleware('auth');
