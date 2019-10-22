<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class PatientEmailController extends Controller
{
    /**
     * @param Request $request
     * @param $patientId
     *
     * @throws \Spatie\MediaLibrary\Exceptions\MediaCannotBeDeleted
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAttachment(Request $request, $patientId)
    {
        $patient = User::findOrFail($patientId);

        $file = $request->file()['file'];

        $media = Media::where('collection_name', 'patient-email-attachments')
            ->where('model_id', $patientId)
            ->where('file_name', str_replace(' ', '-', $file->getClientOriginalName()))
            ->whereIn('model_type', ['App\User', 'CircleLinkHealth\Customer\Entities\User'])
            ->first();

        $patient->deleteMedia($media->id);

        return response()->json(
            [
                'success' => true,
            ],
            200
        );
    }

    public function uploadAttachment(Request $request, $patientId)
    {
        $patient = User::findOrFail($patientId);

        $file = $request->file()['file'];

        $media = $patient->addMedia($file)
            ->withCustomProperties(['doc_type' => 'patient-mail-attachment'])
            ->toMediaCollection('patient-email-attachments');

        return response()->json(
            [
                'media_id' => $media->id,
                'path'     => $media->getPath(),
            ],
            200
        );
    }
}
