<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Services\CPM\CpmLifestyleService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LifestyleController extends Controller
{
    private $lifestyleService;

    /**
     * MedicationController constructor.
     *
     */
    public function __construct(CpmLifestyleService $lifestyleService)
    {
        $this->lifestyleService = $lifestyleService;
    }

    /**
     * returns a list of paginated Medication in the system
     */
    public function index()
    {
        return response()->json($this->lifestyleService->repo()->lifestyles());
    }

    public function show($id)
    {
        return response()->json($this->lifestyleService->repo()->lifestyle($id));
    }
    
    public function patients($id)
    {
        return response()->json($this->lifestyleService->lifestylePatients($id));
    }
}
