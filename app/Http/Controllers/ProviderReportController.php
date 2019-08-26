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

    public function getProviderReport(Request $request, $userId)
    {
        $report = ProviderReport::with('patient.patientInfo')
                                ->where('user_id', '=', $userId)
                                ->firstOrFail();

        $patient = $report->patient;

        if ( ! $patient) {
            throw new \Exception("missing patient from report");
        }

        $reportData = $this->service->formatReportDataForView($report);


        return view('providerReport.report', compact(['reportData', 'patient']));
    }
}
