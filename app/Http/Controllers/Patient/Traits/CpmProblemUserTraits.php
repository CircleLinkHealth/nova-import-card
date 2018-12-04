<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Patient\Traits;

use Illuminate\Http\Request;

trait CpmProblemUserTraits
{
    public function addCpmProblem($userId, Request $request)
    {
        $cpmProblemId = $request->input('cpmProblemId');
        if ($userId && $cpmProblemId) {
            $this->cpmProblemUserService->addProblemToPatient($userId, $cpmProblemId);

            return $this->getCpmProblems($userId);
        }

        return $this->badRequest('"userId" and "cpmProblemId" are important');
    }

    public function getCpmProblems($userId)
    {
        return response()->json($this->cpmProblemUserService->getPatientProblems($userId));
    }

    public function getProblems($userId)
    {
        $cpmProblems = $this->cpmProblemUserService->getPatientProblems($userId)->map(function ($p) {
            $p['type'] = 'cpm';

            return $p;
        });
        $ccdProblems = $this->ccdProblemService->getPatientProblems($userId)->map(function ($p) {
            $p['type'] = 'ccd';

            return $p;
        });

        return response()->json($cpmProblems->concat($ccdProblems));
    }

    public function removeCpmProblem($userId, $cpmId)
    {
        if ($userId && $cpmId) {
            $this->cpmProblemUserService->removeProblemFromPatient($userId, $cpmId);

            return $this->getCpmProblems($userId);
        }

        return $this->badRequest('"userId" and "cpmId" are important');
    }
}
