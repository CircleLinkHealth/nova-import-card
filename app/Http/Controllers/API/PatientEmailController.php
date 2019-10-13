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
    }

    public function uploadAttachment(Request $request, $patientId)
    {
        //for s3?
        $patient = User::findOrFail($patientId);

        //get file
        $file = $request->file()['file'];

        if ($file) {
            $path  = storage_path($file->hashName());
            $saved = $file->store($path);
            if ( ! $saved) {
                return response()->json('Something went wrong, file not saved.', 400);
            }
        }
        //save to S3? maybe we also need to save to S3 to have attachments if we need to backtrack emails to patient sort of like a chat

        return response()->json(
            [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
            ],
            200
        );
    }
}
