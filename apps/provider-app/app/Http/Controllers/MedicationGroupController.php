<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\SharedModels\Services\CPM\CpmMedicationGroupService;

class MedicationGroupController extends Controller
{
    private $medicationGroupService;

    /**
     * MedicationController constructor.
     */
    public function __construct(CpmMedicationGroupService $medicationGroupService)
    {
        $this->medicationGroupService = $medicationGroupService;
    }

    /**
     * returns a list of paginated Medication in the system.
     */
    public function index()
    {
        return response()->json($this->medicationGroupService->repo()->groups());
    }

    public function show($id)
    {
        $group = $this->medicationGroupService->repo()->group($id);
        if ($group) {
            return response()->json($this->medicationGroupService->repo()->group($id));
        }

        return $this->notFound('group with id "'.$id.'" not found');
    }
}
