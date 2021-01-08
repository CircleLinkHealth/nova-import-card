<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\Enrollees;


use CircleLinkHealth\SharedModels\Entities\Enrollee;

class CreateEnrolleesFromPracticePull extends EnrolleeImportingAction
{
    protected int $chunkSize = 200;

    protected function fetchEnrollee(array $row): ?Enrollee
    {
        return Enrollee::firstOrCreate([
            'mrn' => $row['mrn'],
            $this->practiceId
        ]);
    }

    protected function getActionInput(Enrollee $enrollee, array $row): array
    {
        return [
            'email',
            'first_name',
            'last_name',
            'dob',
            'gender',
            'lang',
            'location_id',
            'provider_id',
            'address',
            'address_2',

            'city',
            'state',
            'zip',

            'home_phone',
            'cell_phone',
            'other_phone',
            'primary_insurance',
            'secondary_insurance',
            'status' => Enrollee::TO_CALL,
            'source' => Enrollee::SOURCE_PRACTICE_PULL,
            'last_encounter',
            'facility_name',

            'primary_insurance',
            'secondary_insurance',
            'tertiary_insurance',
            'last_encounter',
            'referring_provider_name',
            'problems',
            'cpm_problem_1',
            'cpm_problem_2',
        ];
    }

    protected function shouldPerformAction(Enrollee $enrollee, array $actionInput): bool
    {
        //maybe check if practice pull data is sufficient?
        return true;
    }

    protected function performAction(Enrollee $enrollee, array $actionInput): void
    {
        $enrollee->update($actionInput);
    }

    protected function validateRow(array $row): bool
    {
        return isset($row['mrn']) && ! empty($row['mrn']);
    }
}