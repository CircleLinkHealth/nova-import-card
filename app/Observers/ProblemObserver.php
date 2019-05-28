<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsToMonitor;
use App\Models\CCD\Problem;

class ProblemObserver
{
    /**
     * Listen to the Problem deleting event.
     *
     * @param Problem $problem
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
     *
     * @param Problem $problem
     */
    public function saving(Problem $problem)
    {
        if ($problem->isDirty('cpm_problem_id')) {
            $patient = $problem->patient;

            if ($patient) {
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
