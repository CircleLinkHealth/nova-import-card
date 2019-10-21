<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientEmailController extends Controller
{
    public function deleteAttachment(Request $request, $patientId)
    {
//        $patient = User::findOrFail($patientId);

        //get file
        $file = $request->file()['file'];

        if ($file) {
            $filePath = storage_path($file->hashName());
            if ($filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
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
        $file = $request->file()['file'];

        if ($file) {
            $path  = storage_path($file->hashName());
            $saved = file_put_contents($path, file_get_contents($file->getPathname()));

            if ( ! $saved || ! file_exists($path)) {
                return response()->json('Something went wrong, file not saved.', 400);
            }
        }
//        $media = $patient->addMedia($file)
//                ->withCustomProperties(['doc_type' => 'patient-mail-attachment'])
//                ->toMediaCollection('patient-mail-attachments');

        return response()->json(
            [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
            ],
            200
        );
    }
}
