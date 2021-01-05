<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectToProviderApp extends Controller
{
    public function ccdImporter()
    {
        return $this->redirectToProvider('ccd-importer');
    }

    public function showPatientDemographics($patientId)
    {
        return $this->redirectToProvider("manage-patients/$patientId/careplan/demographics");
    }
    
    public function notesIndex($patientId)
    {
        return $this->redirectToProvider("manage-patients/$patientId/notes");
    }

    private function redirectToProvider($url)
    {
        return redirect()->to(rtrim(config('core.apps.cpm-provider.url'), '/')."/$url");
    }
}
