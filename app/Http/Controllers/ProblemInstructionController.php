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
        $instructions->getCollection()->transform(function ($value) {
            $value->problems = $value->cpmProblems()->count();
            return $value;
        });
        return response()->json($instructions);
    }
}
