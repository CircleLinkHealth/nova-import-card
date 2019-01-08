<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class PatientCareDocumentsController extends Controller
{
    public function getCareDocuments(Request $request, $patientId, $showPast)
    {

        $patient = User::findOrFail($patientId);

        $files = $patient->getMedia("patient-care-documents")->sortByDesc('created_at')->mapToGroups(function ($item, $key) {
            $docType = $item->getCustomProperty('doc_type');

            return [$docType => $item];
        })->reject(function ($value, $key) {
            return ! $key;
        })->unless($showPast == "true", function ($files){
            return $files->map(function($typeGroup){
                return collect([$typeGroup->first()]);
            });
        })->union(['Vitals' => [0] , 'PPP' => [0], 'Provider Report' => [0], 'Wellness Survey' => [0], 'Lab Results' => [0]]);



        return response()->json($files->toArray());
    }

    public function uploadCareDocuments(Request $request, $patientId)
    {

        $patient = User::findOrFail($patientId);

        foreach ($request->file()['file'] as $file) {

            $patient->addMedia($file)
                    ->withCustomProperties(['doc_type' => $request->doc_type])
                    ->toMediaCollection("patient-care-documents");
        }

        return response()->json([]);
    }

    public function sendAssessmentLink()
    {

    }

    public function downloadCareDocument()
    {
        //get filepath from view and download
    }
}
