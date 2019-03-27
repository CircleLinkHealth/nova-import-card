<?php

namespace App\Http\Controllers;

use App\ProviderReport;
use Illuminate\Http\Request;

class ProviderReportController extends Controller
{
    public function getProviderReport(){

        //get report
        $report = ProviderReport::with('patient.patientInfo')->first();
        $patient = $report->patient;

        return view('providerReport.report', compact(['report', 'patient']));
    }
}
