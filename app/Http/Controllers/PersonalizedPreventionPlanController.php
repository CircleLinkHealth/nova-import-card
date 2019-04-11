<?php

namespace App\Http\Controllers;

use App\PersonalizedPreventionPlan;
use App\Services\SurveyService;
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
        $pppData = PersonalizedPreventionPlan::first();

        if ( ! $pppData) {
            //with message
            return redirect()->back();
        }
      /*  $patient = $pppData->patient;
        if ( ! $patient) {
            //bad data
            return redirect()->back();
        }*/

        return view('personalizedPreventionPlan', compact('pppData'));

    }
}
