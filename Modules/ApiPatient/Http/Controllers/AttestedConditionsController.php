<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\SafeRequest;
use App\Services\CCD\CcdProblemService;
use Carbon\Carbon;
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

    public function update($userId, SafeRequest $request)
    {
        try {
            $date = Carbon::parse('asdasdas')->startOfMonth();
        } catch (\Exception $exception) {
            throw $exception;
        }

        $patient = User::ofType('participant')
            ->with(['patientSummaries' => function ($pms) use ($date) {
                $pms->with('attestedProblems')
                    ->getForMonth($date);
            }])
            ->findOrFail($userId);

        $summary = $patient->patientSummaries->first();

        if ($summary) {
            $summary->syncAttestedProblems($request->input('attested_problems'));
        }

        return response()->json([
            'status'            => 200,
            'attested_problems' => $summary->attestedProblems()->pluck('ccd_problems.id'),
        ]);
    }
}
