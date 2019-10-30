<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Circlelinkhealth\ImportPracticeStaffCsv\CLHImportCardController;
use Illuminate\Support\Facades\Route;

Route::post('/import-csv-to-practice/{resource}', CLHImportCardController::class.'@handle');
