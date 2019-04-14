<?php

namespace App\Http\Controllers;

use App\PersonalizedPreventionPlan;
use App\Services\GeneratePersonalizedPreventionPlanService;
use App\Services\SurveyService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PersonalizedPreventionPlanController extends Controller
{
    const VITALS = 'Vitals';
    const HRA = 'HRA';
    const surveyInstanceVitals = 'Vitals 2019';

    protected $patient;
    private $service;

    public function __construct(SurveyService $service)
    {
        $this->service = $service;
    }

    public function getPppDataForUser(Request $request)
    {

       $this->patient = User::with([
            'patientInfo',
            'billingProvider'
        ])->findOrFail(9784);

        $this->service = new GeneratePersonalizedPreventionPlanService($this->patient);

       $patientPppData = PersonalizedPreventionPlan::findOrFail(2);


        if ( ! $patientPppData) {
            //with message
            return redirect()->back();
        }
     /*    $patient = $patientPppData->patient;
          if ( ! $patient) {
              //bad data
              return redirect()->back();
          }*/

        $birthDate = new Carbon($patientPppData->birth_date);
        $age = now()->diff($birthDate)->y;

        return view('personalizedPreventionPlan', compact('patientPppData', 'age'));

    }
}
