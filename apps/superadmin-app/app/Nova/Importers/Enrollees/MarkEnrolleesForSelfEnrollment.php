<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\Enrollees;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Support\Facades\Validator;

class MarkEnrolleesForSelfEnrollment extends EnrolleeImportingAction
{
    protected int $chunkSize = 200;

    protected function fetchEnrollee(array $row): ?Enrollee
    {
        return Enrollee::with(['enrollmentInvitationLinks'])
            ->whereId($row['eligible_patient_id'])
            ->where('practice_id', $this->practiceId)
            ->where('mrn', $row['mrn'])
            ->where('first_name', $row['first_name'])
            ->where('last_name', $row['last_name'])
            ->first();
    }

    protected function getActionInput(Enrollee $enrollee, array $row): array
    {
        return $row;
    }

    protected function performAction(Enrollee $enrollee, array $actionInput): void
    {
        $enrollee->status                  = Enrollee::QUEUE_AUTO_ENROLLMENT;
        $enrollee->care_ambassador_user_id = null;
        $enrollee->attempt_count           = 0;
        $enrollee->requested_callback      = null;
        $enrollee->save();
    }

    protected function shouldPerformAction(Enrollee $enrollee, array $row): bool
    {
        return Enrollee::QUEUE_AUTO_ENROLLMENT != $enrollee->status && $enrollee->enrollmentInvitationLinks->isEmpty();
    }

    protected function validateRow(array $row): bool
    {
        return Validator::make($row, [
            'eligible_patient_id' => 'required',
            'mrn'                 => 'required',
            'first_name'          => 'required',
            'last_name'           => 'required',
        ])->passes();
    }
}
