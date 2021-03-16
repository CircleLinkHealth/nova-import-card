<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Domain\Patient\AttestPatientProblems;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientIsOfServiceCode;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Http\Requests\SafeRequest;
use CircleLinkHealth\SharedModels\Services\CCD\CcdProblemService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

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
        $patient = app(PatientServiceProcessorRepository::class)->getPatientWithBillingDataForMonth($userId, $date);

        $attestedProblems = $request->input('attested_problems');

        if (PatientIsOfServiceCode::fromDTO(
            PatientMonthlyBillingDTO::generateFromUser($patient, $date),
            ChargeableService::BHI
        )) {
            $attestedProblems = array_merge(
                $attestedProblems,
                $patient->attestedProblems->where(
                    'cpmProblem.is_behavioral',
                    '=',
                    ! $request->input('is_bhi')
                )->pluck('id')->toArray()
            );
        }

        (new AttestPatientProblems())->forPms(
            optional($patient->patientSummaries->first())->id
        )
            ->forMonth($date)
            ->fromAttestor(Auth::id())
            ->problemsToAttest($attestedProblems)
            ->setSyncing(true)
            ->createRecords();

        return response()->json([
            'status'            => 200,
            'attested_problems' => $request->input('attested_problems'),
        ]);
    }
}
