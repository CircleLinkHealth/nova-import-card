<?php

namespace App\Http\Controllers;

use App\PersonalizedPreventionPlan;
use App\Services\PersonalizedPreventionPlanPrepareData;
use Illuminate\Http\Request;

class PersonalizedPreventionPlanController extends Controller
{
    protected $service;

    public function __construct(PersonalizedPreventionPlanPrepareData $service)
    {
        $this->service = $service;
    }

    public function getPppDataForUser(Request $request)
    {//id 9784 is just for testing. Will the provider review & edit the PPP and then send it or it will be sent automatically?
        $patientPppData = PersonalizedPreventionPlan::where('patient_id', 9784)
                                                    ->with('patient.patientInfo')
                                                    ->first();
        if ( ! $patientPppData) {
            //with message
            return redirect()->back();
        }
        $patient = $patientPppData->patient;

        if ( ! $patient) {
            //bad data
            return redirect()->back();
        }

        $personalizedHealthAdvices = $this->service->prepareRecommendations($patientPppData);

        return view('personalizedPreventionPlan', compact('personalizedHealthAdvices', 'patient'));
    }
}
