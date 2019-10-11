<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\ImportPracticeStaffCsv;

use Circlelinkhealth\ImportPracticeStaffCsv\ImportPracticeStaffCsvNovaRequest;

class CLHImportCardController
{
    public function handle(ImportPracticeStaffCsvNovaRequest $request)
    {
        return redirect()->action('Sparclex\NovaImportCard\ImportController@handle', ['request' => $request]);
    }
}
