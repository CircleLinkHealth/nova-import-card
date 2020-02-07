<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ImportPracticeStaffCsv;

use Sparclex\NovaImportCard\ImportController;

class CLHImportCardController
{
    /**
     * We need a post request made to Sparclex\NovaImportCard\ImportController@handle
     * but we cannot redirect to a POST request.
     *
     *This is a hacky workaround.
     *
     * @param ImportPracticeStaffCsvNovaRequest $request
     *
     * @return array
     */
    public function handle(ImportPracticeStaffCsvNovaRequest $request)
    {
        $controller = new ImportController();

        return $controller->handle($request);
    }
}
