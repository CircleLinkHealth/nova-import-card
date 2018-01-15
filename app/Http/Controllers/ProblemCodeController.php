<?php

namespace App\Http\Controllers;

use App\Services\ProblemCodeService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;


class ProblemCodeController extends Controller
{
    private $problemCodeService;

    /**
     * MedicationController constructor.
     *
     */
    public function __construct(ProblemCodeService $problemCodeService)
    {   
        $this->problemCodeService = $problemCodeService;
    }

    /**
     * returns a list of code-systems
     */
    public function index()
    {
        return response()->json($this->problemCodeService->systems());
    }

    public function show($id)
    {
        return response()->json($this->problemCodeService->system($id));
    }
}
