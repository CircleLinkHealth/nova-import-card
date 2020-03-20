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
     *
     * @param CcdProblemService $ccdProblemService
     */
    public function __construct(CcdProblemService $ccdProblemService)
    {
        $this->ccdProblemService = $ccdProblemService;
    }

    public function update($userId, SafeRequest $request)
    {
        try {
            $date = Carbon::createFromFormat('F, Y', $request->input('date'))->startOfMonth();
        } catch (\Exception $exception) {
            throw $exception;
        }

        $patient = User::ofType('participant')
                       ->with(['patientSummaries' => function ($pms) use ($date) {
                           $pms->with('attestedProblems')
                               ->getForMonth($date);
                       }])
                       ->findOrFail($userId);

        $attestedProblems = $request->input('attested_problems');

        $summary = $patient->patientSummaries->first();

        $attestedProblems = array_merge($attestedProblems, $summary->attestedProblems->where('cpmProblem.is_behavioral', '=', ! $request->input('is_bhi'))->pluck('id')->toArray());

        if ($summary) {
            $summary->syncAttestedProblems($attestedProblems);
        }

        return response()->json([
            'status'            => 200,
            'attested_problems' => $summary->attestedProblems()->get()->where('cpmProblem.is_behavioral', '=', $request->input('is_bhi'))->pluck('id')->toArray(),
        ]);
    }
}