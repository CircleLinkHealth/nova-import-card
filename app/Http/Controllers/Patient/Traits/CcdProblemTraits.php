<?php

namespace App\Http\Controllers\Patient\Traits;

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
        }
        else return $this->badRequest('"userId" and "ccdId" are important');
    }
    
    public function addCcdProblem($userId, Request $request)
    {
        $name = $request->input('name');
        $cpm_problem_id = $request->input('cpm_problem_id');
        if ($userId && $name) {
            return response()->json($this->ccdProblemService->addPatientCcdProblem($userId, $name, $cpm_problem_id));
        }
        else return $this->badRequest('"userId" and "name" are important');
    }
    
    public function editCcdProblem($userId, $ccdId, Request $request)
    {
        $name = $request->input('name');
        $cpm_problem_id = $request->input('cpm_problem_id');
        if ($cpm_problem_id && $name) {
            return response()->json($this->ccdProblemService->editPatientCcdProblem($userId, $ccdId, $name, $cpm_problem_id));
        }
        else return $this->badRequest('"userId" and "name" are important');
    }
}
