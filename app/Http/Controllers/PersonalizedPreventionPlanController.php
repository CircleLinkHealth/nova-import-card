<?php

namespace App\Http\Controllers;

use App\PersonalizedPreventionPlan;
use App\Services\PersonalizedPreventionPlanPrepareData;

class PersonalizedPreventionPlanController extends Controller
{
    protected $service;

    public function __construct(PersonalizedPreventionPlanPrepareData $service)
    {
        $this->service = $service;
    }

    public function getPppDataForUser($userId, $year = null)
    {
        $patientPppData = PersonalizedPreventionPlan::where('user_id', '=', $userId)
                                                    ->with('patient')
                                                    ->firstOrFail();

        $patient = $patientPppData->patient;

        $personalizedHealthAdvices = $this->service->prepareRecommendations($patientPppData);

        return view('personalizedPreventionPlan', compact('personalizedHealthAdvices', 'patient', 'patientPppData'));
    }
}
