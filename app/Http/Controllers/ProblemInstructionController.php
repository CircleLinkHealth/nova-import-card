<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Patient;
use App\Http\Controllers\Controller;
use App\Models\CPM\CpmInstruction;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ProblemInstructionController extends Controller
{
    /**
     * ProblemInstructionController constructor.
     *
     */
    public function __construct()
    {

    }

    public function index() {
        $instructions = CpmInstruction::paginate(15);
        $instructions->getCollection()->transform([$this, 'setupInstruction']);
        return response()->json($instructions);
    }

    public function instruction($instructionId) {
        $instruction = CpmInstruction::where('id', $instructionId)->first();
        if ($instruction) return response()->json($this->setupInstruction($instruction));
        else return response()->json([
            'message' => 'not found'
        ]);
    }

    function setupInstruction($value) {
        $value->problems = $value->cpmProblems()->count();
        return $value;
    }
}
