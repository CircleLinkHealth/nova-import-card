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
    public function uploadAttachment(Request $request, $patientId)
    {
        $patient = User::findOrFail($patientId);

        //get file
        $file = $request->file()['file'];

        //store file locally and return path to storage
        //save to S3? maybe we also need to save to S3 to have attachments if we need to backtrack emails to patient sort of like a chat

        return response()->json([]);
    }
}
