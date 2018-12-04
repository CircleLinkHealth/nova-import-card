<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\Faker\Patient;

use App\Models\CCD\Problem as CcdProblem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use App\User;
use Illuminate\Database\Eloquent\Collection;

class Problem
{
    /**
     * attaches problems to user.
     *
     * @param User $patient
     *
     * @return User
     */
    public function attachProblemSet(User $patient)
    {
        $problemSet = $this->problemSet(false);

        //sometimes returns error TODO
        $patient->ccdProblems()->saveMany($problemSet);

        return $patient;
    }

    public function getCcdProblems(): Collection
    {
        //taking 2000 to save memory
        return CcdProblem::take(2000)->get();
    }

    public function getCpmProblems(): Collection
    {
        return CpmProblem::take(2000)->get();
    }

    public function getProblemCodes(): Collection
    {
        return ProblemCode::take(2000)->get();
    }

    /**
     * returns `ccd_problem`.
     *
     * @param bool $withCodes
     * @param null $name
     *
     * @return int
     */
    public function problem($withCodes = true, $name = null)
    {
        if (true == $withCodes) {
            if ($name) {
                $cpmProblem        = $this->getCpmProblems()->firstWhere('name', $name);
                $ccdProblem        = $this->getCcdProblems()->firstWhere('cpm_problem_id', $cpmProblem->id);
                $problemCodes      = $this->getProblemCodes()->where('problem_id', $ccdProblem->id);
                $ccdProblem->codes = $problemCodes;
            } else {
                $ccdProblem   = $this->getCcdProblems()->random();
                $problemCodes = $this->getProblemCodes()->where('problem_id', $ccdProblem->id);

                $ccdProblem->codes = $problemCodes;
            }

            return $ccdProblem;
        }
        if ($name) {
            $cpmProblem = $this->getCpmProblems()->firstWhere('name', $name);
            $ccdProblem = $this->getCcdProblems()->firstWhere('cpm_problem_id', $cpmProblem->id);
        } else {
            $ccdProblem = $this->getCcdProblems()->random();
        }

        return $ccdProblem;
    }

    /**
     * returns array of `ccd_problem` for sample patients.
     *
     * @param bool $withCodes
     *
     * @return array
     */
    public function problemSet($withCodes = true)
    {
        $patientIds = $this->getCcdProblems()->pluck('patient_id');
        $patientId  = $patientIds->random();
        $problemSet = [];

        if ($withCodes) {
            $ccdProblems = $this->getCcdProblems()->where('patient_id', $patientId);
            foreach ($ccdProblems as $ccdProblem) {
                $problemCodes = $this->getProblemCodes()->where('problem_id', $ccdProblem->id);
                if ($problemCodes) {
                    $ccdProblem->codes = $problemCodes;
                    $problemSet[]      = $ccdProblem;
                } else {
                    $problemSet[] = $ccdProblem;
                }
            }

            return $problemSet;
        }

        return $this->getCcdProblems()->where('patient_id', $patientId);
    }
}
