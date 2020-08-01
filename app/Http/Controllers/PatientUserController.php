<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Contracts\ReportFormatter;
use App\Relationships\PatientCareplanRelations;
use App\Services\CareplanService;
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
        $patient = auth()->user()->load(PatientCareplanRelations::get());

        $careplan = collect($this->reportFormatter->formatDataForViewPrintCareplanReport($patient))->first();

        return view(
            'wpUsers.patient.careplan.print-patient',
            [
                'patient'       => $patient,
                'careplan'      => $careplan,
                'problemNames'  => $careplan['problem'],
                'careTeam'      => $patient->careTeamMembers,
                'data'          => $this->careplanService->careplan($patient->id),
                'billingDoctor' => $patient->billingProviderUser(),
                'regularDoctor' => $patient->regularDoctorUser(),
            ]
        );
    }
}
