<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Services\CPM\CpmMedicationService;
use App\Services\CPM\CpmMedicationGroupService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MedicationGroupController extends Controller
{
    private $medicationGroupService;

    /**
     * MedicationController constructor.
     *
     */
    public function __construct(CpmMedicationGroupService $medicationGroupService)
    {
        $this->medicationGroupService = $medicationGroupService;
    }

    /**
     * returns a list of paginated Medication in the system
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
        } else {
            return $this->notFound('group with id "' . $id . '" not found');
        }
    }
}
