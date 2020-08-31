<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Http\Requests\SafeRequest;
use App\Services\CCD\CcdProblemService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Routing\Controller;

class AttestedConditionsController extends Controller
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

    public function getUniqueConditionsToAttest($userId, SafeRequest $request)
    {
        $allCcdProblems = $this->ccdProblemService->getPatientProblems($userId)->filter(function ($p) {
            return $p['is_monitored'];
        });

        $uniqueCode = $allCcdProblems->unique(function ($p) {
            return $p['code'];
        });

        $uniqueCodeAndName = $uniqueCode->unique(function ($p) {
            return strtolower($p['name']);
        });

        return \response()->json($uniqueCodeAndName->values());
    }

    public function update($userId, SafeRequest $request)
    {
        try {
            $date = Carbon::createFromFormat('F, Y', $request->input('date'))->startOfMonth();
        } catch (\Exception $exception) {
            throw $exception;
        }

        $patient = User::ofType('participant')
            ->with([
                'patientSummaries' => function ($pms) use ($date) {
                    $pms->with('attestedProblems')
                        ->getForMonth($date);
                },
            ])
            ->findOrFail($userId);

        $attestedProblems = $request->input('attested_problems');

        $summary = $patient->patientSummaries->first();

        if ( ! $summary) {
            //The request comes from ABP page. Patient should have summary, else throw Exception.
            throw new \Exception("Patient {$patient->id} does not have a summary for month {$date->toDateString()->startOfMonth()}.");
        }

        //if summary does not have BHI all codes will be included in request from the CCM, no need to merge.
        //else merge and attest, but return only the ones actually used by modal
        if ($summary->hasServiceCode(ChargeableService::BHI)) {
            $attestedProblems = array_merge(
                $attestedProblems,
                $summary->attestedProblems->where(
                    'cpmProblem.is_behavioral',
                    '=',
                    ! $request->input('is_bhi')
                )->pluck('id')->toArray()
            );
        }

        $summary->syncAttestedProblems($attestedProblems);

        return response()->json([
            'status'            => 200,
            'attested_problems' => $request->input('attested_problems'),
        ]);
    }
}
