<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\SafeRequest;
use App\Services\CCD\CcdProblemService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class CcdProblemController extends Controller
{
    /**
     * @var CcdProblemService
     */
    protected $ccdProblemService;

    /**
     * CcdProblemController constructor.
     *
     * @param CcdProblemService $ccdProblemService
     */
    public function __construct(CcdProblemService $ccdProblemService)
    {
        $this->ccdProblemService = $ccdProblemService;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('apipatient::create');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int   $id
     * @param mixed $userId
     * @param mixed $ccdProblemId
     *
     * @return Response
     */
    public function destroy($userId, $ccdProblemId)
    {
        if ($userId && $ccdProblemId) {
            return $this->jsonResponse(\App\Models\CCD\Problem::where(['patient_id' => $userId, 'id' => $ccdProblemId])->delete());
        }

        return $this->jsonResponse('"userId" and "ccdProblemId" are important', 400);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('apipatient::index');
    }

    /**
     * Show the specified resource.
     *
     * @param int   $id
     * @param mixed $userId
     *
     * @return Response
     */
    public function show($userId)
    {
        return $this->jsonResponse($this->ccdProblemService->getPatientProblems($userId));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param mixed   $userId
     *
     * @return Response
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

        return $this->jsonResponse($this->ccdProblemService->addPatientCcdProblem($ccdProblem));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     * @param mixed   $userId
     * @param mixed   $ccdProblemId
     *
     * @return Response
     */
    public function update($userId, $ccdProblemId, SafeRequest $request)
    {
        $cpm_problem_id = $request->inputSafe('cpm_problem_id');
        $is_monitored   = $request->inputSafe('is_monitored');
        $icd10          = $request->inputSafe('icd10');
        $instruction    = $request->inputSafe('instruction');
        if ($ccdProblemId) {
            return $this->jsonResponse($this->ccdProblemService->editPatientCcdProblem($userId, $ccdProblemId, $cpm_problem_id, $is_monitored, $icd10, $instruction));
        }

        return $this->jsonResponse('"userId" and "ccdProblemId" are important', 400);
    }

    private function jsonResponse($data = [], $status = 200, array $headers = [], $options = 0)
    {
        return \response()->json($data, $status, $headers, $options);
    }
}
