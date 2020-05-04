<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\PersonalizedPreventionPlanPrepareData;
use App\User;
use Carbon\Carbon;

class PersonalizedPreventionPlanController extends Controller
{
    protected $service;

    public function __construct(PersonalizedPreventionPlanPrepareData $service)
    {
        $this->service = $service;
    }

    public function getPppForUser($userId, $year = null)
    {
        if ( ! $year) {
            $year = Carbon::now()->year;
        }

        $patient = User::with([
            'patientInfo',
            'personalizedPreventionPlan' => function ($report) use ($year) {
                $report->forYear($year);
            },
        ])
            ->findOrFail($userId);

        $patientPppData = optional($patient->personalizedPreventionPlan)->first();

        if ( ! $patientPppData) {
            throw new \Exception("This patient does not have a PPP for {$year}.");
        }

        $personalizedHealthAdvices = $this->service->prepareRecommendations($patientPppData);

        $suggestedCheckListData = $this->service->getOrderedSuggestedChecklist($personalizedHealthAdvices);

        return view('reports.ppp', compact('personalizedHealthAdvices', 'patient', 'patientPppData', 'suggestedCheckListData'));
    }
}
