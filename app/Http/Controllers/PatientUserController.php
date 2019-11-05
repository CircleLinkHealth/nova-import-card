<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Contracts\ReportFormatter;
use App\Services\CareplanService;
use App\ValueObjects\PatientCareplanRelations;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class PatientUserController extends Controller
{
    protected $careplanService;
    protected $reportFormatter;

    public function __construct(ReportFormatter $reportFormatter, CareplanService $careplanService)
    {
        $this->reportFormatter = $reportFormatter;
        $this->careplanService = $careplanService;
    }

    public function viewCareplan(
        Request $request
    ) {
        $patient = User::with(PatientCareplanRelations::get())->findOrFail(auth()->user()->id);

        $careplan = $this->reportFormatter->formatDataForViewPrintCareplanReport($patient);
        $careplan = $careplan[$patient->id];

        //change check, make cleaner
        if (empty($careplan)) {
            throw new \Exception("Could not get CarePlan info for CarePlan with ID: {$patient->id}");
        }

        return view(
            'wpUsers.patient.careplan.print-patient',
            [
                'careplans'    => [$patient->id => $careplan],
                'isPdf'        => true,
                'letter'       => false,
                'problemNames' => $careplan['problem'],
                'careTeam'     => $patient->careTeamMembers,
                'data'         => $this->careplanService->careplan($patient->id),
            ]
        );
    }
}
