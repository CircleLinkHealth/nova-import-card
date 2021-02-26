<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\Eligibility\Jobs\ImportConsentedEnrollees;
use CircleLinkHealth\SelfEnrollment\Http\Requests\ImportEnrolleesFromSelfEnrollmentRequest;

class ImportSelfEnrollmentEnrollees extends Controller
{
    public function runImportConsentedEnrollees(ImportEnrolleesFromSelfEnrollmentRequest $request)
    {
        $enrolleeId = $request->input('enrolleeId');

        try {
            ImportConsentedEnrollees::dispatch([$enrolleeId]);

            return response()->json([
                'message' => 'job dispatched',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => "Importing from SelfEnrollment failed for enrollee [$enrolleeId][{$exception->getMessage()}]",
            ], 400);
        }
    }
}
