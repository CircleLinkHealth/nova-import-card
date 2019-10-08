<?php

namespace App\Http\Controllers;

use App\ProviderReport;
use App\Services\ProviderReportService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class ProviderReportController extends Controller
{
    protected $service;

    public function __construct(ProviderReportService $service)
    {
        $this->service = $service;
    }

    public function getProviderReport($userId, $year = null)
    {
        if (! $year){
            $year = Carbon::now()->year;
        }

        $patient = User::with([
            'patientInfo',
            'providerReports' => function ($report) use ($year) {
                $report->forYear($year);
            },
        ])
                       ->findOrFail($userId);

        $report = optional($patient->providerReports)->first();

        if (! $report){
            throw new \Exception("This patient does not have a Provider Report for {$year}.");
        }

        $reportData = $this->service->formatReportDataForView($report);

        return view('providerReport.report', compact(['reportData', 'patient']));
    }
}
