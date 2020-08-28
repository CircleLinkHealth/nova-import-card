<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Http\Requests\SafeRequest;
use App\Services\CCD\CcdProblemService;
use Illuminate\Routing\Controller;

class CcdProblemController extends Controller
{
    /**
     * @var CcdProblemService
     */
    protected $ccdProblemService;

    /**
     * CcdProblemController constructor.
     */
    public function __construct(CcdProblemService $ccdProblemService)
    {
        $this->ccdProblemService = $ccdProblemService;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param mixed $userId
     * @param mixed $ccdProblemId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($userId, $ccdProblemId)
    {
        if ($userId && $ccdProblemId) {
            return \response()->json(
                \CircleLinkHealth\SharedModels\Entities\Problem::where(['patient_id' => $userId, 'id' => $ccdProblemId])->delete()
            );
        }

        return \response()->json('"userId" and "ccdProblemId" are important', 400);
    }

    /**
     * Show the specified resource.
     *
     * @param mixed $userId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($userId)
    {
        return \response()->json($this->ccdProblemService->getPatientProblems($userId));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param mixed $userId
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($userId, SafeRequest $request)
    {
        $ccdProblem = [
            'name'           => $request->inputSafe('name'),
            'cpm_problem_id' => $request->inputSafe('cpm_problem_id'),
            'userId'         => $userId,
            'is_monitored'   => $request->inputSafe('is_monitored'),
            'icd10'          => $request->inputSafe('icd10'),
        ];

        return \response()->json($this->ccdProblemService->addPatientCcdProblem($ccdProblem));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param mixed $userId
     * @param mixed $ccdProblemId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($userId, $ccdProblemId, SafeRequest $request)
    {
        $cpm_problem_id = $request->inputSafe('cpm_problem_id');
        $is_monitored   = $request->inputSafe('is_monitored');
        $icd10          = $request->inputSafe('icd10');
        $instruction    = $request->inputSafe('instruction');
        if ($ccdProblemId) {
            return \response()->json($this->ccdProblemService->editPatientCcdProblem($userId, $ccdProblemId, $cpm_problem_id, $is_monitored, $icd10, $instruction));
        }

        return \response()->json('"userId" and "ccdProblemId" are important', 400);
    }
}
