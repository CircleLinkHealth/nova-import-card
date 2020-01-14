<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\CCDModels\Items;

use App\Http\Controllers\Controller;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Http\Request;

/**
 * Class ProblemListItemController.
 */
class ProblemsItemController extends Controller
{
    public function destroy(Request $request)
    {
        $problem = $request->input('problem');
        if ( ! empty($problem)) {
            $ccdProblem = Problem::find($problem['id']);
            if ( ! $ccdProblem) {
                return response('Problem '.$problem['id'].' not found', 401);
            }
            $ccdProblem->delete();
        }

        return response('Successfully removed Problem');
    }

    public function index(Request $request)
    {
        $data        = [];
        $patientId   = $request->input('patient_id');
        $ccdProblems = Problem::where('patient_id', '=', $patientId)->orderBy('name')->get();
        if ($ccdProblems->count() > 0) {
            foreach ($ccdProblems as $ccdProblem) {
                $data[] = [
                    'id'         => $ccdProblem->id,
                    'patient_id' => $ccdProblem->patient_id,
                    'name'       => $ccdProblem->name, ];
            }
        }
        // return a JSON response
        return response()->json($data);
    }

    public function store(Request $request)
    {
        // pass back some data, along with the original data, just to prove it was received
        $problem = $request->input('problem');
        if ( ! empty($problem)) {
            $ccdProblem             = new Problem();
            $ccdProblem->patient_id = $problem['patient_id'];
            $ccdProblem->name       = $problem['name'];
            $ccdProblem->save();
            $id = $ccdProblem;
        }
        $result = ['id' => $id];
        // return a JSON response
        return response()->json($result);
    }

    public function update(Request $request)
    {
        // pass back some data, along with the original data, just to prove it was received
        $problem = $request->input('problem');
        if ( ! empty($problem)) {
            $ccdProblem = Problem::find($problem['id']);
            if ( ! $ccdProblem) {
                return response('Problem not found', 401);
            }
            $ccdProblem->name = $problem['name'];
            $ccdProblem->save();
        }
        $string = '';
        // return a JSON response
        return response()->json($string);
    }
}
