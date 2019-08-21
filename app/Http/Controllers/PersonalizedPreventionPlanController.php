<?php

namespace App\Http\Controllers;

use App\PersonalizedPreventionPlan;
use App\Services\PersonalizedPreventionPlanPrepareData;
use App\User;

class PersonalizedPreventionPlanController extends Controller
{
    protected $service;

    public function __construct(PersonalizedPreventionPlanPrepareData $service)
    {
        $this->service = $service;
    }

    public function getPppDataForUser()
    {//ENTER A VALID PATIENT
        $patientPppData = PersonalizedPreventionPlan::where('user_id', '=', 13278)
            ->first();
        $patient = User::find(13278);

        $personalizedHealthAdvices = $this->service->prepareRecommendations($patientPppData);

        return view('personalizedPreventionPlan', compact('personalizedHealthAdvices',  'patient', 'patientPppData'));
    }
}
