<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

Route::post('/import-csv-to-practice/{resource}', 'CircleLinkHealth\ImportPracticeStaffCsv\CLHImportCardController@handle');
