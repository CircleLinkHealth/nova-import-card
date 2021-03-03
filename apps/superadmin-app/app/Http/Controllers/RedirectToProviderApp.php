<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class RedirectToProviderApp extends Controller
{
    public function ccdImporter()
    {
        return $this->redirectToProvider('ccd-importer');
    }

    public function notesIndex($patientId)
    {
        return $this->redirectToProvider("manage-patients/$patientId/notes");
    }

    public function notesShow($patientId, $noteId)
    {
        return $this->redirectToProvider("manage-patients/$patientId/notes/view/$noteId");
    }

    public function showCareplan($patientId)
    {
        return $this->redirectToProvider("manage-patients/$patientId/view-careplan");
    }

    public function showPatientDemographics($patientId)
    {
        return $this->redirectToProvider("manage-patients/$patientId/careplan/demographics");
    }

    private function redirectToProvider($url)
    {
        return redirect()->to(rtrim(config('core.apps.cpm-provider.url'), '/')."/$url");
    }
}
