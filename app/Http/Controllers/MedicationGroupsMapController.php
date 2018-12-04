<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\MedicationGroupsMap;
use App\Models\CPM\CpmMedicationGroup;
use Illuminate\Http\Request;

class MedicationGroupsMapController extends Controller
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
        MedicationGroupsMap::destroy($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\ResponseX
     */
    public function index()
    {
        $medicationGroups = CpmMedicationGroup::all()->sortBy('name');
        $maps             = MedicationGroupsMap::with('cpmMedicationGroup')->get()->sortBy('keyword')->values();

        return view('admin.medicationGroupsMaps.index', compact('medicationGroups', 'maps'));
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $stored = MedicationGroupsMap::create($request->input());
        $stored->load('cpmMedicationGroup');

        return response()->json([
            'stored' => $stored,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(
        Request $request,
        $id
    ) {
    }
}
