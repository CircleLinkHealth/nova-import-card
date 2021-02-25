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
        } catch (\Exception $exception) {
            return response()
                ->setStatusCode(500, "Importing from SelfEnrollment failed for enrollee [$enrolleeId][{$exception->getMessage()}]");
        }
    }
}
