<?php

namespace App\Http\Controllers\Patient\Traits;

use App\SafeRequest;
use Illuminate\Http\Request;

trait CcdProblemTraits
{
    public function getCcdProblems($userId)
    {
        return response()->json($this->ccdProblemService->getPatientProblems($userId));
    }
    
    public function removeCcdProblem($userId, $ccdId)
    {
        if ($userId && $ccdId) {
            return response()->json($this->ccdProblemService->repo()->removePatientCcdProblem($userId, $ccdId));
        } else {
            return $this->badRequest('"userId" and "ccdId" are important');
        }
    }
    
    public function addCcdProblem($userId, SafeRequest $request)
    {
        $ccdProblem = [
                        'name' => $request->inputSafe('name'),
                        'cpm_problem_id' => $request->inputSafe('cpm_problem_id'),
                        'userId' => $userId,
                        'is_monitored' => $request->inputSafe('is_monitored'),
                        'icd10' => $request->inputSafe('icd10')
                    ];
        return response()->json($this->ccdProblemService->addPatientCcdProblem($ccdProblem));
    }
    
    public function editCcdProblem($userId, $ccdId, SafeRequest $request)
    {
        $name = $request->inputSafe('name');
        $cpm_problem_id = $request->inputSafe('cpm_problem_id');
        $is_monitored = $request->inputSafe('is_monitored');
        $icd10 = $request->inputSafe('icd10');
        $instruction = $request->inputSafe('instruction');
        if ($name) {
            return response()->json($this->ccdProblemService->editPatientCcdProblem($userId, $ccdId, $name, $cpm_problem_id, $is_monitored, $icd10, $instruction));
        } else {
            return $this->badRequest('"userId" and "name" are important');
        }
    }
}
