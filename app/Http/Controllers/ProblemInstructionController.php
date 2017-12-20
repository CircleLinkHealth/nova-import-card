<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Patient;
use App\User;
use App\Http\Controllers\Controller;
use App\Models\CPM\CpmInstruction;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Models\CPM\CpmProblemUser;
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

    /** returns paginated list of cpm-instructions */
    public function index() {
        $instructions = CpmInstruction::paginate(15);
        $instructions->getCollection()->transform([$this, 'setupInstruction']);
        return response()->json($instructions);
    }

    /** returns a single cpm-instruction */
    public function instruction($instructionId) {
        $instruction = CpmInstruction::where('id', $instructionId)->first();
        if ($instruction) return response()->json($this->setupInstruction($instruction));
        else return $this->notFound();
    }

    /** creates a cpm-instruction */
    public function store(Request $request) {
        $name = $request->input('name');
        if ($name && $name != '') {
            try {
                $instruction = new CpmInstruction();
                $instruction->name = $name;
                $instruction->is_default = 0;
                $instruction->save();
                return response()->json($instruction);
            }
            catch (Exception $ex) {
                return $this->error('error when creating new instruction', $ex);
            }
        }
        else {
            return $this->badRequest('please provide a value for the [name] parameter');
        }
    }

    /** edits an existing cpm-instruction */
    public function edit(Request $request) {
        $id = $request->route()->id;
        $name = $request->input('name');
        $is_default = $request->input('is_default');
        if ($id && $id != '') {
            $instructions = CpmInstruction::where('id', $id);
            if ($name && $name != '') $instructions->update(['name' => $name]);
            if ($is_default) $instructions->update(['is_default' => $is_default]);

            $instruction = $instructions->first();
            if ($instruction) return response()->json($instruction);
            else return $this->notFound();
        }
        else {
            return $this->badRequest('please provide a value for the [id] parameter');
        }
    }

    public function addInstructionProblem(Request $request) {
        $patientId = $request->route()->patientId;
        $cpmProblemId = $request->route()->cpmId;
        $instructionId = $request->input('instructionId');

        $patient = User::where('id', $patientId)->first();
        $problem = CpmProblem::where('id', $cpmProblemId)->first();
        $instruction = CpmInstruction::where('id', $instructionId)->first();

        if ($patient && $problem && $instruction) {
            $cpmInstruction = CpmProblemUser::where('patient_id', $patientId)
                                        ->where('cpm_problem_id', $cpmProblemId)
                                        ->where('cpm_instruction_id', $instructionId)->first();
            if (!$cpmInstruction) {
                $cpmProblemUser= new CpmProblemUser();
                $cpmProblemUser->patient_id = $patientId;
                $cpmProblemUser->cpm_problem_id = $cpmProblemId;
                $cpmProblemUser->cpm_instruction_id = $instructionId;
                $cpmProblemUser->save();
                return response()->json($cpmProblemUser);
            }
            else {
                return $this->conflict('a similar instruction->problem relationship already exists');
            }
        }
        else {
            if (!$patient) return $this->notFound('patient not found');
            else if (!$problem) return $this->notFound('cpm problem not found');
            else return $this->notFound('instruction not found');
        }
    }

    function setupInstruction($value) {
        $value->problems = $value->cpmProblems()->get(['cpm_problems.id'])->map(function ($p) {
            return $p->id;
        });
        return $value;
    }
}
