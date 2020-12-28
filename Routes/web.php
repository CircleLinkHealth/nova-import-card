<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::get('health-check', 'HealthCheckController@isSiteUp');
Route::get('opcache', 'OPCacheGUIController@index')->middleware('permission:admin-access');
