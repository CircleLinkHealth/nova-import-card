<?php

namespace App\Http\Controllers;

use App\PersonalizedPreventionPlan;
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

        if (! $year){
            $year = Carbon::now()->year;
        }

        $patient = User::with([
            'patientInfo',
            'personalizedPreventionPlan' => function ($report) use ($year) {
                $report->forYear($year);
            },
        ])
                       ->findOrFail($userId);

        $ppp = $patient->personalizedPreventionPlan->first();

        if (! $ppp){
            throw new \Exception("This patient does not have a PPP for {$year}.");
        }

        $personalizedHealthAdvices = $this->service->prepareRecommendations($ppp);

        return view('personalizedPreventionPlan', compact('personalizedHealthAdvices', 'patient', 'patientPppData'));
    }
}
