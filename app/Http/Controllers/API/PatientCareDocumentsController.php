<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\Models\Media;

class PatientCareDocumentsController extends Controller
{
    public function getCareDocuments(Request $request, $patientId, $showPast)
    {
        $patient = User::findOrFail($patientId);

        $files = $patient->getMedia("patient-care-documents")->sortByDesc('created_at')->mapToGroups(function (
            $item,
            $key
        ) {
            $docType = $item->getCustomProperty('doc_type');

            return [$docType => $item];
        })->reject(function ($value, $key) {
            return ! $key;
        })
            //get the latest file from each category
                         ->unless($showPast == "true", function ($files) {
                return $files->map(function ($typeGroup) {
                    return collect([$typeGroup->first()]);
                });
            });

        return response()->json($files->toArray());
    }

    public function uploadCareDocuments(Request $request, $patientId)
    {

        $patient = User::findOrFail($patientId);

        foreach ($request->file()['file'] as $file) {

            if ($file->getMimeType() !== "application/pdf"){
                return response()->json(
                    'The file you are trying to upload is not a PDF.'
                    , 400);
            }
            //if file is larger than 10MB
            if ($file->getSize() > 10000000){
                return response()->json(
                    'The file you are trying to upload is too large.'
                    , 400);
            }
            $patient->addMedia($file)
                    ->withCustomProperties(['doc_type' => $request->doc_type])
                    ->toMediaCollection("patient-care-documents");
        }


        return response()->json([]);
    }

    public function sendAssessmentLink()
    {

    }

    public function downloadCareDocument($id, $mediaId)
    {
        $mediaItem = User::findOrFail($id)->getMedia('patient-care-documents')->where('id', $mediaId)->first();

        if (!$mediaItem){
            throw new \Exception('Media for Patient does not exist.', 500);
        }

        return response($mediaItem->getFile(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$mediaItem->name.'"',
        ]);
    }

}
