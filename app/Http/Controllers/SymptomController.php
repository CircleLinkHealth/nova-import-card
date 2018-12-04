<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Services\CPM\CpmSymptomService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SymptomController extends Controller
{
    private $symptomService;

    /**
     * MedicationController constructor.
     *
     */
    public function __construct(CpmSymptomService $symptomService)
    {
        $this->symptomService = $symptomService;
    }

    /**
     * returns a list of paginated Medication in the system
     */
    public function index()
    {
        return response()->json($this->symptomService->symptoms());
    }
}
