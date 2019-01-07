<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PatientCareDocumentsController extends Controller
{
    public function getCareDocuments(Request $request, $patientId){
        //get current care docs
        //return json with caredoc details not actual files
        $patient = User::findOrFail($patientId);

        $files = $patient->getMedia("patient-care-documents");

        return response()->json($files->all());
    }

    public function uploadCareDocuments(Request $request, $patientId, $docType = 'test'){

        $patient = User::findOrFail($patientId);


        foreach ($request->file()['file'] as $file) {

            $patient->addMedia($file->path())
                ->usingFileName('otherFileName.txt')
                                 ->toMediaCollection("patient-care-documents");
        }

        return response()->json([]);
    }

    public function sendAssessmentLink(){

    }

    public function downloadCareDocument(){
        //get filepath from view and download
    }
}
