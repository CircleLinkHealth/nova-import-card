<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Services\CPM\CpmMiscService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    private $miscService;

    /**
     * MedicationController constructor.
     *
     */
    public function __construct(CpmMiscService $miscService)
    {
        $this->miscService = $miscService;
    }

    /**
     * returns a list of paginated Medication in the system
     */
    public function index()
    {
        return response()->json($this->miscService->repo()->misc());
    }

    public function show($id)
    {
        return response()->json($this->miscService->repo()->misc($id));
    }
    
    public function patients($id)
    {
        return response()->json($this->miscService->miscPatients($id));
    }

    public function test()
    {
        return response()->json([
            'message' => 'clh'
        ]);
    }
}
