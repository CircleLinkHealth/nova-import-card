<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ManageCpmProblemsController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $problem = CpmProblem::where('id', $request['problem_id'])->first();

        return view('admin.problemKeywords.edit', compact(['problem']));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $problems = CpmProblem::get()->sortBy('name');

        return view('admin.problemKeywords.index', compact(['problems']));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $problem = CpmProblem::find($request['problem_id']);

        if ($problem->contains == $request['contains'] &&
            $problem->default_icd_10_code == $request['default_icd_10_code'] &&
            $problem->is_behavioral == $request['is_behavioral'] &&
            $problem->weight == $request['weight']) {
            return redirect()->route('manage-cpm-problems.edit', ['problem_id' => $problem->id])->with(
                'msg',
                'No changes have been made.'
            );
        }
        $data = [
            'contains'            => $request['contains'],
            'default_icd_10_code' => $request['default_icd_10_code'],
            'is_behavioral'       => $request['is_behavioral'],
            'weight'              => $request['weight'],
        ];
        $problem->update($data);

        return redirect()->route('manage-cpm-problems.edit', ['problem_id' => $problem->id])->with(
            'msg',
            'Changes Successfully Applied.'
        );
    }
}
