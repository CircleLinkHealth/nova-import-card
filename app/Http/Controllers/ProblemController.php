<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Patient;
use App\Http\Controllers\Controller;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use Carbon\Carbon;
use Illuminate\Http\Request;


class ProblemController extends Controller
{
    /**
     * ProblemController constructor.
     *
     */
    public function __construct()
    {

    }

    function getCpmProblems() {
        return CpmProblem::where('name', '!=', 'Diabetes')
                    ->get()
                    ->map(function ($p) {
                        return [
                            'id'   => $p->id,
                            'name' => $p->name,
                            'code' => $p->default_icd_10_code,
                        ];
                    });
    }

    /**
     * returns a list of Problems in the system
     */
    public function index()
    {
        $cpmProblems = $this->getCpmProblems();
        return response()->json($cpmProblems);
    }
}
