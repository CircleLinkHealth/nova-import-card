<?php

namespace App\Http\Controllers;

use App\Models\CPM\CpmProblem;
use Illuminate\Http\Request;

class ManageCpmProblemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $problems = CpmProblem::get()->sortBy('name');

        $problem = null;


        return view('admin.problemKeywords.index', compact(['problems', 'problem',]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {


        $problem = CpmProblem::where('id', $request['problem_id'])->first();

        $message = null;


        return view('admin.problemKeywords.edit', compact(['problem']));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if ($request['problem_id'] == null) {
            return back();
        }
        $problem = CpmProblem::find($request['problem_id']);
        $data    = [
            'contains'            => $request['contains'],
            'default_icd_10_code' => $request['default_icd_10_code'],
            'is_behavioral'       => $request['is_behavioral'],
            'weight'              => $request['weight'],
        ];

        $problem->update($data);

        return redirect()->route('manage-cpm-problems.edit', ['problem_id' => $problem->id])->with('msg',
            'Changes Successfully Applied.');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
