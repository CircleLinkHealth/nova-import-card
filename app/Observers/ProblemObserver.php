<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsToMonitor;
use App\Models\CCD\Problem;
use App\User;

class ProblemObserver
{
    /**
     * Listen to the Problem deleting event.
     *
     * @param Problem $problem
     */
    public function deleted(Problem $problem)
    {
        $patient = User::with('ccdProblems')->where('id', $problem->patient_id)->first();

        $storage = new ProblemsToMonitor($patient->program_id, $patient);

        $problems           = $patient->ccdProblems;
        $problemsToActivate = [];

        foreach ($problems as $problem) {
            if (empty($problem->cpm_problem_id)) {
                continue;
            }

            $problemsToActivate[] = $problem->cpm_problem_id;
        }

        $storage->import(array_unique($problemsToActivate), true);
    }

    /**
     * Listen to the Problem saved event.
     *
     * @param Problem $problem
     */
    public function saved(Problem $problem)
    {
        if ($problem->isDirty('cpm_problem_id')) {
            $patient = User::find($problem->patient_id);

            if ($patient) {
                $storage = new ProblemsToMonitor($patient->program_id, $patient);

                $storage->import($problem->cpm_problem_id);
            }
        }
    }
}
