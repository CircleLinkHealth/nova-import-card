<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\Enrollees;

use CircleLinkHealth\Eligibility\Adapters\PracticePullToEnrolleeAdapter;
use CircleLinkHealth\SharedModels\Entities\Enrollee;

class CreateEnrolleesFromPracticePull extends EnrolleeImportingAction
{
    protected array $importingErrors = [];
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
        if (! isset($actionInput['provider_id']) || is_null($actionInput['provider_id'])){
            $this->importingErrors[$this->rowNumber] = 'Failed to match provider';
        }

        if (empty($actionInput)){
            $this->importingErrors[$this->rowNumber] = 'Failed to fetch Practice Pull Data.';
            return false;
        }

        return true;
    }

    protected function validateRow(array $row): bool
    {
        return isset($row['mrn']) && ! empty($row['mrn']);
    }

    public function __destruct()
    {
        if ( ! empty($this->importingErrors)) {
            $rowErrors = collect($this->importingErrors)->transform(function ($item, $key) {
                return "Row: {$key} - Errors: {$item}. ";
            })->implode('\n');

            sendSlackMessage('#cpm_general_alerts', "{$this->getErrorMessageIntro()} "."\n"."{$rowErrors}");
        }
    }

    /**
     * The message that is displayed before each row error is listed.
     */
    protected function getErrorMessageIntro(): string
    {
        return "The following rows from queued job to create enrollable Patients for Practice with ID:'{$this->practiceId}',
            from file {$this->fileName} had some problems. See below:";
    }
}
