<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\SharedModels\Services\CPM\CpmLifestyleService;

class LifestyleController extends Controller
{
    private $lifestyleService;

    /**
     * MedicationController constructor.
     */
    public function __construct(CpmLifestyleService $lifestyleService)
    {
        $this->lifestyleService = $lifestyleService;
    }

    /**
     * returns a list of paginated Medication in the system.
     */
    public function index()
    {
        return response()->json($this->lifestyleService->repo()->lifestyles());
    }

    public function patients($id)
    {
        return response()->json($this->lifestyleService->lifestylePatients($id));
    }

    public function show($id)
    {
        return response()->json($this->lifestyleService->repo()->lifestyle($id));
    }
}
