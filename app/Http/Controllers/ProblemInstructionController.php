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
        else return $this->notFound();
    }

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

    function setupInstruction($value) {
        $value->problems = $value->cpmProblems()->count();
        return $value;
    }
}
