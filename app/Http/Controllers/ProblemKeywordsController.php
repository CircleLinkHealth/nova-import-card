<?php

namespace App\Http\Controllers;

use App\Models\CPM\CpmProblem;
use Illuminate\Http\Request;

class ProblemKeywordsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $problems = CpmProblem::get();

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $problems = CpmProblem::get();

        $problem = $problems->where('id', $request['problem_id'])->first();

        $message = null;


        return view('admin.problemKeywords.index', compact(['problems', 'problem',]));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if ($request['problemId'] == null){
            return back();
        }
        $problems = CpmProblem::get();
        $problem = CpmProblem::find($request['problemId']);
        $contains = $request['contains'];

        $message = 'You need to make some changes to the keywords';

        if ($problem->contains != $contains){
            $problem->contains = $contains;
            $problem->save();
            $message = 'Keywords successfully edited!';
        }





        return view('admin.problemKeywords.index', compact(['problems', 'problem', 'message']));


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
