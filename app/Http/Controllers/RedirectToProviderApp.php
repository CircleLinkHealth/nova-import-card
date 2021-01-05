<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\SharedModels\Entities\Note;

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
    
    public function noteId($noteId)
    {
        $patientId = Note::whereId($noteId)->value('patient_id');
        
        return $this->redirectToProvider("manage-patients/$patientId/notes/view/$noteId");
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
