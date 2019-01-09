<?php

namespace App\Http\Controllers;

use App\NurseWeeklyRep;
use Illuminate\Http\Request;

class NursesWeeklyRepController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.reports.nurseweekly');
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
     * @param  \App\NurseWeeklyRep  $nurseWeeklyRep
     * @return \Illuminate\Http\Response
     */
    public function show(NurseWeeklyRep $nurseWeeklyRep)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\NurseWeeklyRep  $nurseWeeklyRep
     * @return \Illuminate\Http\Response
     */
    public function edit(NurseWeeklyRep $nurseWeeklyRep)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\NurseWeeklyRep  $nurseWeeklyRep
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NurseWeeklyRep $nurseWeeklyRep)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\NurseWeeklyRep  $nurseWeeklyRep
     * @return \Illuminate\Http\Response
     */
    public function destroy(NurseWeeklyRep $nurseWeeklyRep)
    {
        //
    }
}
