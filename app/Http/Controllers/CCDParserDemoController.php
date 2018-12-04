<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\ReportsService;
use App\User;
use Illuminate\Support\Facades\App;

class CCDParserDemoController extends Controller
{
    public function index()
    {
        $patientId     = 308;
        $reportService = new ReportsService();
        $careplan      = $reportService->carePlanGenerator([$patientId]);

        $pdf = App::make('snappy.pdf.wrapper');

        $pdf->loadView('wpUsers.patient.careplan.print', [
            'patient'             => User::find($patientId),
            'treating'            => $careplan[$patientId]['treating'],
            'biometrics'          => $careplan[$patientId]['bio_data'],
            'symptoms'            => $careplan[$patientId]['symptoms'],
            'lifestyle'           => $careplan[$patientId]['lifestyle'],
            'medications_monitor' => $careplan[$patientId]['medications'],
            'taking_medications'  => $careplan[$patientId]['taking_meds'],
            'allergies'           => $careplan[$patientId]['allergies'],
            'social'              => $careplan[$patientId]['social'],
            'appointments'        => $careplan[$patientId]['appointments'],
            'other'               => $careplan[$patientId]['other'],
            'isPdf'               => true,
        ]);

        $pdf->save(base_path('storage/pdfs/hello.pdf'), true);
    }
}
