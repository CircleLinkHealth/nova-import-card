<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\Enrollees;


use CircleLinkHealth\SharedModels\Entities\Enrollee;

class MarkEnrollesAsIneligible extends EnrolleeImportingAction
{
    protected int $chunkSize = 200;

    protected function fetchEnrollee(array $row) :? Enrollee
    {
        return Enrollee::where('mrn', $row['mrn'])
                       ->where('practice_id', $this->practiceId)
                       ->first();
    }

    protected function shouldPerformAction(Enrollee $enrollee, array $row): bool
    {
        return true;
    }

    protected function performAction(Enrollee $enrollee) : void
    {
        $enrollee->status = Enrollee::INELIGIBLE;
        $enrollee->save();
    }

    protected function validateRow(array $row): bool
    {
        return isset($row['mrn']);
    }
}