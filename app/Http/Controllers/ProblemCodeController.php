<?php

namespace App\Http\Controllers;

use App\Models\ProblemCode;
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

    public function store(Request $request)
    {
        $problemCode = new ProblemCode();
        $problemCode->problem_id = $request->input('problem_id');
        $problemCode->problem_code_system_id = $request->input('problem_code_system_id');
        $problemCode->code = $request->code;
        return response()->json($this->problemCodeService->add($problemCode));
    }

    public function remove($id)
    {
        return response()->json($this->problemCodeService->repo()->remove($id));
    }
}
