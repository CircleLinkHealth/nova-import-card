<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Patient\Traits;

use App\SafeRequest;

trait CcdProblemTraits
{
    public function addCcdProblem($userId, SafeRequest $request)
    {
        $ccdProblem = [
            'name'           => $request->inputSafe('name'),
            'cpm_problem_id' => $request->inputSafe('cpm_problem_id'),
            'userId'         => $userId,
            'is_monitored'   => $request->inputSafe('is_monitored'),
            'icd10'          => $request->inputSafe('icd10'),
        ];

        return response()->json($this->ccdProblemService->addPatientCcdProblem($ccdProblem));
    }

    public function editCcdProblem($userId, $ccdProblemId, SafeRequest $request)
    {
        $name           = $request->inputSafe('name');
        $cpm_problem_id = $request->inputSafe('cpm_problem_id');
        $is_monitored   = $request->inputSafe('is_monitored');
        $icd10          = $request->inputSafe('icd10');
        $instruction    = $request->inputSafe('instruction');
        if ($name) {
            return response()->json($this->ccdProblemService->editPatientCcdProblem($userId, $ccdProblemId, $name, $cpm_problem_id, $is_monitored, $icd10, $instruction));
        }

        return $this->badRequest('"userId" and "name" are important');
    }

    public function getCcdProblems($userId)
    {
        return response()->json($this->ccdProblemService->getPatientProblems($userId));
    }

    public function removeCcdProblem($userId, $ccdId)
    {
        if ($userId && $ccdId) {
            return response()->json($this->ccdProblemService->repo()->removePatientCcdProblem($userId, $ccdId));
        }

        return $this->badRequest('"userId" and "ccdId" are important');
    }
}
