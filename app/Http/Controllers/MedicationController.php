<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\SharedModels\Services\CPM\CpmMedicationService;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    private $medicationService;

    /**
     * MedicationController constructor.
     */
    public function __construct(CpmMedicationService $medicationService)
    {
        $this->medicationService = $medicationService;
    }

    /**
     * returns a list of paginated Medication in the system.
     */
    public function index()
    {
        return response()->json($this->medicationService->medications());
    }

    public function search(Request $request)
    {
        $term = $request->input('term');
        if ($term) {
            $terms = explode(',', $term);

            return response()->json($this->medicationService->search($terms));
        }

        return $this->badRequest('missing parameter: "term"');
    }
}
