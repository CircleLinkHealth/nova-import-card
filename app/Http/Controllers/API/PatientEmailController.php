<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Rules\PatientEmailDoesNotContainPhi;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class PatientEmailController extends Controller
{
    /**
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

        $name = str_replace('.'.$file->getClientOriginalExtension(), '', $file->getClientOriginalName());

        $media = Media::where('collection_name', 'patient-email-attachments')
            ->where('model_id', $patientId)
            ->where('name', $name)
            ->whereIn('model_type', [\App\User::class, 'CircleLinkHealth\Customer\Entities\User'])
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

        if ($file) {
            //check if media exists, if yes return it
            $media = $patient->addMedia($file)
                ->withCustomProperties(['doc_type' => 'patient-mail-attachment'])
                ->toMediaCollection('patient-email-attachments');

            return response()->json(
                [
                    'media_id' => $media->id,
                    'path'     => $media->getPath(),
                    'url'      => $media->getFullUrl(),
                    'name'     => $file->getClientOriginalName(),
                ],
                200
            );
        }

        return response()->json(
            [
                'success' => false,
            ],
            400
        );
    }

    public function validateEmailBody(Request $request, $patientId)
    {
        $validator = \Validator::make($request->input(), [
            'patient_email_subject' => [
                'sometimes',
                new PatientEmailDoesNotContainPhi(User::findOrFail($patientId)),
            ],
            'patient_email_body' => [
                'sometimes',
                new PatientEmailDoesNotContainPhi(User::findOrFail($patientId)),
            ],
        ]);

        return response()->json([
            'status' => $validator->passes()
                ? 200
                : 400,
            'messages' => collect($validator->getMessageBag()->toArray())->flatten()->toArray()
                ?: '',
        ]);
    }
}
