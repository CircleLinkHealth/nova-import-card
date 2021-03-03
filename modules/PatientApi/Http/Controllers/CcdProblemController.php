<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use CircleLinkHealth\CcmBilling\Domain\Patient\PatientServicesForTimeTracker;
use CircleLinkHealth\CcmBilling\Domain\Patient\ProcessPatientBillingStatus;
use CircleLinkHealth\CcmBilling\Domain\Patient\ProcessPatientSummaries;
use CircleLinkHealth\Customer\Http\Requests\SafeRequest;
use CircleLinkHealth\Patientapi\ValueObjects\CcdProblemInput;
use CircleLinkHealth\SharedModels\Services\CCD\CcdProblemService;
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
            $success = $this->ccdProblemService->deletePatientCcdProblem(
                (new CcdProblemInput())
                    ->setUserId($userId)
                    ->setCcdProblemId($ccdProblemId)
            );

            $this->doMonthlyProcessing($userId);

            return \response()->json([
                'success'             => $success,
                'chargeable_services' => $this->getChargeableServices($userId),
            ]);
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
        $problem = $this->ccdProblemService->addPatientCcdProblem(
            (new CcdProblemInput())
                ->fromRequest($request->allSafe())
                ->setUserId($userId)
        );

        $this->doMonthlyProcessing($userId);

        return \response()->json([
            'problem'             => $problem,
            'chargeable_services' => $this->getChargeableServices($userId),
        ]);
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
        if ($ccdProblemId) {
            $problem = $this->ccdProblemService->editPatientCcdProblem(
                (new CcdProblemInput())
                    ->fromRequest($request->allSafe())
                    ->setUserId($userId)
                    ->setCcdProblemId($ccdProblemId)
            );

            $this->doMonthlyProcessing($userId);

            return \response()->json([
                'problem'             => $problem,
                'chargeable_services' => $this->getChargeableServices($userId),
            ]);
        }

        return \response()->json('"userId" and "ccdProblemId" are important', 400);
    }

    private function doMonthlyProcessing($userId)
    {
        $month = now()->startOfMonth();
        (app(ProcessPatientSummaries::class))->execute($userId, $month);
    }

    private function getChargeableServices($patientId)
    {
        return (new PatientServicesForTimeTracker((int) $patientId, now()))->get();
    }
}
