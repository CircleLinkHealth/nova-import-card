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
    {
        $patientPppData = PersonalizedPreventionPlan::with('patient.patientInfo')->first();

        if ( ! $patientPppData) {
            //with message
            return redirect()->back();
        }
        $patient = $patientPppData->patient;

            if ( ! $patient) {
              //bad data
              return redirect()->back();
          }
        $reportData = $this->service->prepareRecommendations($patientPppData);

        $recommendationTasks = collect();
        foreach ($reportData['recommendation_tasks'] as $key => $tasks) {
            $recommendationTasks[$key] = $tasks;
        }
        $personalizedHealthAdvices = $recommendationTasks->map(function ($recommendation) {
            $tasks = array_slice($recommendation, 1);
            $tableData=[];
            foreach ($tasks as $task) {
                if ( ! empty($task['report_table_data'])) {
                    $tableData = $task['report_table_data'];
                }
            }
                    return [
                        'title' => $recommendation[0],
                        'tasks' => $tasks,
                        'table_data' => $tableData,
                    ];
        });

        return view('personalizedPreventionPlan', compact( 'personalizedHealthAdvices', 'patient'));
    }
}
