<?php

namespace App\Http\Controllers;

use App\ProviderReport;
use App\Services\ProviderReportService;
use Illuminate\Support\Facades\Request;

class ProviderReportController extends Controller
{
    protected $service;

    public function __construct(ProviderReportService $service)
    {
        $this->service = $service;
    }

    public function getProviderReport(Request $request)
    {
        //Placeholder code to test. We should get patient id, report id OR date OR hra instance id + vitals instance id. Will create request class when we know for sure.
        $report = ProviderReport::with('patient.patientInfo')->first();

        if ( ! $report) {
            //with message
            return redirect()->back();
        }
        $patient = $report->patient;

        if ( ! $patient) {
            //bad data
            return redirect()->back();
        }

        $reportData = $this->service->formatReportDataForView($report);


        return view('providerReport.report', compact(['reportData', 'patient']));
    }
}
