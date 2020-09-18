<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\ClhImportCardExtended\CLHImportCardController;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Card API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your card. These routes
| are loaded by the ServiceProvider of your card. You're free to add
| as many additional routes to this file as your card may require.
|
*/

// This file is part of CarePlan Manager by CircleLink Health.

use Illuminate\Support\Facades\Route;

Route::post('/import-csv-to-resource/{resource}', CLHImportCardController::class.'@handle');
