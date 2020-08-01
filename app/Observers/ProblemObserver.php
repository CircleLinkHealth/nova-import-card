<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\ProblemsToMonitor;
use CircleLinkHealth\SharedModels\Entities\Problem;

class ProblemObserver
{
    /**
     * Listen to the Problem deleting event.
     */
    public function deleting(Problem $problem)
    {
        $patient = $problem->patient;

        if ($patient) {
            $storage = new ProblemsToMonitor($patient->program_id, $patient);

            $storage->detach($problem->cpm_problem_id);
        }
    }

    /**
     * Listen to the Problem saving event.
     */
    public function saving(Problem $problem)
    {
        if ($problem->isDirty('cpm_problem_id')) {
            $patient = $problem->patient;

            $patient->loadMissing('carePlan');

            if ($patient && $patient->carePlan) {
                $storage = new ProblemsToMonitor($patient->program_id, $patient);

                $originalCpmProblemId = $problem->getOriginal('cpm_problem_id');
                // If only the cpm_problem_id is changed (if the problem is being updated), we need to detach relationships for that problem first
                if ($originalCpmProblemId) {
                    $storage->detach($originalCpmProblemId);
                }

                $storage->import($problem->cpm_problem_id);
            }
        }
    }
}
