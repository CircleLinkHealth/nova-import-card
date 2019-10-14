<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\ImportPracticeStaffCsv;

use Sparclex\NovaImportCard\ImportController;

class CLHImportCardController
{
    public function handle(ImportPracticeStaffCsvNovaRequest $request)
    {
        //not possible to redirect to POST
        $controller = new ImportController();

        return $controller->handle($request);
//        return redirect()->action('\Sparclex\NovaImportCard\ImportController@handle', ['request' => $request]);
    }
}
