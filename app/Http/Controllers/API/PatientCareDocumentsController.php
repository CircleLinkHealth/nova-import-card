<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class PatientCareDocumentsController extends Controller
{
    /**
     * The size of ten megabytes, used for file validation.
     */
    const TEN_MB = 10485760;

    public function downloadCareDocument($id, $mediaId)
    {
        $mediaItem = $this->getMediaItemById($id, $mediaId);

        if ( ! $mediaItem) {
            throw new \Exception('Media for Patient does not exist.', 500);
        }

        return $this->downloadMedia($mediaItem);
    }

    public function getCareDocuments(Request $request, $patientId, $showPast = false)
    {
        $patientAWVStatuses = PatientAWVSurveyInstanceStatus::where('patient_id', $patientId)
            ->when( ! $showPast, function ($query) {
                                                                $query->where('year', Carbon::now()->year);
                                                            })
            ->get();

        $files = Media::where('collection_name', 'patient-care-documents')
            ->where('model_id', $patientId)
            ->whereIn('model_type', ['App\User', 'CircleLinkHealth\Customer\Entities\User'])
            ->get()
            ->sortByDesc('created_at')
            ->mapToGroups(function ($item, $key) {
                          $docType = $item->getCustomProperty('doc_type');

                          return [$docType => $item];
                      })
            ->reject(function ($value, $key) {
                          return ! $key;
                      })
            //get the latest file from each category
            ->unless('true' == $showPast, function ($files) {
                          return $files->map(function ($typeGroup) {
                              return collect([$typeGroup->first()]);
                          });
                      });

        return response()->json([
            'files'              => $files->toArray(),
            'patientAWVStatuses' => $patientAWVStatuses->toArray(),
        ]);
    }

    public function sendCareDocument($patientId, $mediaId, $channel, $addressOrFax)
    {
        $media = $this->getMediaItemById($patientId, $mediaId);

        if ('email' == $channel) {
            //validate email

            //send notification

            //notification contains url with patientId, report type (from media), and year (from media created at), create endpoint to handle in AWV

            return response()->json(
                'Document sent successfully!',
                200
            );
        }

        return response()->json(
            'This feature has not yet been implemented',
            400
        );
    }

    public function uploadCareDocuments(Request $request, $patientId)
    {
        $patient = User::findOrFail($patientId);

        foreach ($request->file()['file'] as $file) {
            if ('application/pdf' !== $file->getMimeType()) {
                return response()->json(
                    'The file you are trying to upload is not a PDF.',
                    400
                );
            }
            //if file is larger than 10MB
            if ($file->getSize() > $this::TEN_MB) {
                return response()->json(
                    'The file you are trying to upload is too large.',
                    400
                );
            }
            $patient->addMedia($file)
                ->withCustomProperties(['doc_type' => $request->doc_type])
                ->toMediaCollection('patient-care-documents');
        }

        return response()->json([]);
    }

    public function viewCareDocument($id, $mediaId)
    {
        $mediaItem = $this->getMediaItemById($id, $mediaId);

        if ( ! $mediaItem) {
            throw new \Exception('Media for Patient does not exist.', 500);
        }

        return response($mediaItem->getFile(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$mediaItem->name.'"',
        ]);
    }

    private function getMediaItemById($modelId, $mediaId)
    {
        return Media::where('collection_name', 'patient-care-documents')
            ->where('model_id', $modelId)
            ->whereIn('model_type', ['App\User', 'CircleLinkHealth\Customer\Entities\User'])
            ->find($mediaId);
    }
}
