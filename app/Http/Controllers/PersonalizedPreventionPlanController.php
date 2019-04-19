<?php

namespace App\Http\Controllers;

use App\PersonalizedPreventionPlan;
use App\Services\GeneratePersonalizedPreventionPlanService;
use App\Services\PersonalizedPreventionPlanPrepareData;
use App\Services\SurveyService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PersonalizedPreventionPlanController extends Controller
{
    protected $patient;
    protected $service;

    public function __construct(PersonalizedPreventionPlanPrepareData $service)
    {
        $this->service = $service;
    }

    public function getPppDataForUser(Request $request)
    {
      $patientPppData = PersonalizedPreventionPlan::with('patient.patientInfo')->find(34);

        if ( ! $patientPppData) {
            //with message
            return redirect()->back();
        }
      $patient = $patientPppData->patient;
        /*     if ( ! $patient) {
              //bad data
              return redirect()->back();
          }*/

        $birthDate = new Carbon($patientPppData->birth_date);
        $age = now()->diff($birthDate)->y;
        $reportData = $this->service->prepareRecommendations($patientPppData);
dd($reportData);
        return view('personalizedPreventionPlan', compact('reportData', 'age'));

    }
}
