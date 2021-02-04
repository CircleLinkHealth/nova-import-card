<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Observers;

use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;

class PatientMonthlySummaryObserver
{
    public function creating(PatientMonthlySummary $record)
    {
        if ( ! $record->problem_1 || ! $record->problem_2) {
            $existingRecord = PatientMonthlySummary::wherePatientId($record->patient_id)
                ->where('id', '!=', $record->id)
                ->whereApproved(true)
                ->orderBy('id', 'DESC')
                ->first();

            if ($existingRecord) {
                if ($existingRecord->problem_1 && ! $record->problem_1) {
                    $record->problem_1              = $existingRecord->problem_1;
                    $record->billable_problem1      = $existingRecord->billable_problem1;
                    $record->billable_problem1_code = $existingRecord->billable_problem1_code;
                }
                if ($existingRecord->problem_2 && ! $record->problem_2) {
                    $record->problem_2              = $existingRecord->problem_2;
                    $record->billable_problem2      = $existingRecord->billable_problem2;
                    $record->billable_problem2_code = $existingRecord->billable_problem2_code;
                }
            }
        }
    }
}
