<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Services\CPM\CpmMedicationService;
use App\Services\CPM\CpmMedicationGroupService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    private $medicationService;

    /**
     * MedicationController constructor.
     *
     */
    public function __construct(CpmMedicationService $medicationService)
    {
        $this->medicationService = $medicationService;
    }

    /**
     * returns a list of paginated Medication in the system
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
        } else {
            return $this->badRequest('missing parameter: "term"');
        }
    }
}
