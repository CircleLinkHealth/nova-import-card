<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Circlelinkhealth\GeneratePatientCallDataCsv\GeneratePatientCallDataCsvController;
use Illuminate\Support\Facades\Route;

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

Route::get('/generate-csv-for-month/{monthYear}', GeneratePatientCallDataCsvController::class.'@handle');
