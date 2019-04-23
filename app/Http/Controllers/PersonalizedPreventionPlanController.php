<?php

namespace App\Http\Controllers;

use App\PersonalizedPreventionPlan;
use App\Services\PersonalizedPreventionPlanPrepareData;
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

        $birthDate  = new Carbon($patientPppData->birth_date);
        $age        = now()->diff($birthDate)->y;
        $reportData = $this->service->prepareRecommendations($patientPppData);

        $recommendationTasks = collect([]);
        foreach ($reportData['recommendation_tasks'] as $key => $tasks) {
          unset($tasks['title']);
            $recommendationTasks[$key] = $tasks;
        }
        /* $recommendations = [];
         foreach ($recommendationTasks as $recommendationTask) {
             foreach ($recommendationTask as $key => $item) {
                 $recommendations[] = $item;
             }
         }*/
        /*foreach ($recommendationTasks['nutrition_recommendations']as $item) {
            dd($item['task_body']);
       }*/

        //dd($recommendationTasks);
        return view('personalizedPreventionPlan', compact('reportData', 'age', 'recommendationTasks'));/*->with([
            'reportData'      => $reportData,
            'age'             => $age,
            'recommendations' => $recommendations,
        ]);*/
    }
}
