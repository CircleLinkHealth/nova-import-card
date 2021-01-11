<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\Enrollees;

use CircleLinkHealth\Eligibility\Adapters\PracticePullToEnrolleeAdapter;
use CircleLinkHealth\SharedModels\Entities\Enrollee;

class CreateEnrolleesFromPracticePull extends EnrolleeImportingAction
{
    protected int $chunkSize = 200;

    protected function fetchEnrollee(array $row): ?Enrollee
    {
        return Enrollee::firstOrCreate([
            'mrn'         => $row['mrn'],
            'practice_id' => $this->practiceId,
        ]);
    }

    protected function getActionInput(Enrollee $enrollee, array $row): array
    {
        return PracticePullToEnrolleeAdapter::getArray((string) $row['mrn'], $this->practiceId);
    }

    protected function performAction(Enrollee $enrollee, array $actionInput): void
    {
        $enrollee->update($actionInput);
    }

    protected function shouldPerformAction(Enrollee $enrollee, array $actionInput): bool
    {
        return ! empty($actionInput);
    }

    protected function validateRow(array $row): bool
    {
        return isset($row['mrn']) && ! empty($row['mrn']);
    }
}
