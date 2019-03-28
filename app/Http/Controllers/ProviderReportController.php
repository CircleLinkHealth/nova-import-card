<?php

namespace App\Http\Controllers;

use App\ProviderReport;
use Illuminate\Support\Facades\Request;

class ProviderReportController extends Controller
{
    public function getProviderReport(Request $request)
    {
        //placeholder code
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

        return view('providerReport.report', compact(['report', 'patient']));
    }
}
