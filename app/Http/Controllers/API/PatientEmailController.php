<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Rules\PatientEmailDoesNotContainPhi;
use App\Rules\ValidatePatientCustomEmail;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $name = $file->getClientOriginalName();
        $ext  = '.'.$file->getClientOriginalExtension();

        if (Str::endsWith($name, $ext)) {
            $name = rtrim($name, $ext);
        }

        $media = Media::where('collection_name', 'patient-email-attachments')
            ->where('model_id', $patientId)
            ->where('name', $name)
            ->whereIn('model_type', [\App\User::class, User::class])
            ->first();

        if ($media) {
            $patient->deleteMedia($media->id);
        }
        
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
        $patient = User::findOrFail($patientId);

        //todo: add default email validation as well (needs more refactoring will do in ROAD-235)
        $validator = \Validator::make($request->input(), [
            'patient_email_subject' => [
                'sometimes',
                new PatientEmailDoesNotContainPhi($patient),
            ],
            'patient_email_body' => [
                'sometimes',
                new PatientEmailDoesNotContainPhi($patient),
            ],
            'custom_patient_email' => [
                'sometimes',
                new ValidatePatientCustomEmail(),
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
