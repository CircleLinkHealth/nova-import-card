<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\CLH\CCD\Importer\StorageStrategies\Problems\ProblemsToMonitor;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
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
        //exclude generic diabetes
        $diabetes = CpmProblem::where('name', 'Diabetes')->first();

        $patient = User::with([
            'ccdProblems' => function ($p) use ($diabetes) {
                $p->where('cpm_problem_id', '!=', $diabetes->id);
            },
        ])
            ->where('id', $problem->patient_id)->first();

        $storage = new ProblemsToMonitor($patient->program_id, $patient);

        $problemsToActivate = $this->getProblemsToActivate($patient);

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
            //exclude generic diabetes
            $diabetes = CpmProblem::where('name', 'Diabetes')->first();

            $patient = User::with([
                'ccdProblems' => function ($p) use ($diabetes) {
                    $p->where('cpm_problem_id', '!=', $diabetes->id);
                },
            ])
                ->where('id', $problem->patient_id)->first();

            if ($patient) {
                $storage = new ProblemsToMonitor($patient->program_id, $patient);

                // If only the cpm_problem_id is changed, we sync relationships (symptoms, lifestyles etc)  for all problems again,
                if ( ! $problem->isDirty(['patient_id', 'name'])) {
                    $problemsToActivate = $this->getProblemsToActivate($patient);

                    $storage->import(array_unique($problemsToActivate), true);
                } else {
                    // else we are just importing the new problem's relationships.
                    $storage->import($problem->cpm_problem_id);
                }
            }
        }
    }

    protected function getProblemsToActivate($patient)
    {
        $problems           = $patient->ccdProblems;
        $problemsToActivate = [];

        foreach ($problems as $problem) {
            if (empty($problem->cpm_problem_id)) {
                continue;
            }

            $problemsToActivate[] = $problem->cpm_problem_id;
        }

        return $problemsToActivate;
    }
}
