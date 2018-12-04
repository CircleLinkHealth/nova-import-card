<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\CPM\CpmSymptomService;

class SymptomController extends Controller
{
    private $symptomService;

    /**
     * MedicationController constructor.
     */
    public function __construct(CpmSymptomService $symptomService)
    {
        $this->symptomService = $symptomService;
    }

    /**
     * returns a list of paginated Medication in the system.
     */
    public function index()
    {
        return response()->json($this->symptomService->symptoms());
    }
}
