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

    public function getPppDataForUser(Request $request, $userId)
    {
        //Will the provider review & edit the PPP and then send it or it will be sent automatically?
        $patientPppData = PersonalizedPreventionPlan::where('patient_id', $userId)
                                                    ->with('patient.patientInfo')
                                                    ->first();

        if ( ! $patientPppData) {
            return redirect()
                ->withErrors(["message" => "Could not find report for user id[$userId]"])
                ->back();
        }
        $patient = $patientPppData->patient;
        if ( ! $patient) {
            return redirect()
                ->withErrors(["message" => "There was an error"])
                ->back();
        }

        $personalizedHealthAdvices = $this->service->prepareRecommendations($patientPppData);

        return view('personalizedPreventionPlan', compact('personalizedHealthAdvices', 'patient', 'patientPppData'));
    }
}
