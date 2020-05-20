<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Hooks;

use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportHook;

class FetchMedicationsFromAthenaApi extends BaseCcdaImportHook
{
    const IMPORTING_LISTENER_NAME = 'fetch_medications_from_athena_api';
    
    public function run()
    {
    
    }
}