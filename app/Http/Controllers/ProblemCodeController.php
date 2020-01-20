<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\SharedModels\Entities\ProblemCode;
use CircleLinkHealth\SharedModels\Entities\ProblemCodeSystem;
use Illuminate\Http\Request;

class ProblemCodeController extends Controller
{
    /**
     * returns a list of code-systems.
     */
    public function index()
    {
        return response()->json(ProblemCodeSystem::get());
    }

    public function remove($id)
    {
        abort_if_str_contains_unsafe_characters($id);

        $deleted = ProblemCode::where('id', $id)->delete();

        return response()->json([
            'message' => $deleted ? 'successful' : 'unsuccesful',
        ]);
    }

    public function show($id)
    {
        abort_if_str_contains_unsafe_characters($id);

        return response()->json(ProblemCodeSystem::findOrFail($id));
    }

    public function store(Request $request)
    {
        $problemCode                         = new ProblemCode();
        $problemCode->problem_id             = $request->input('problem_id');
        $problemCode->problem_code_system_id = $request->input('problem_code_system_id');
        $problemCode->code                   = $request->code;
        $problemCode->resolve();
        $problemCode->save();

        return response()->json($problemCode);
    }
}
