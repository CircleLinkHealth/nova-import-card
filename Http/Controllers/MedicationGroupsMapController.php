<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicationGroupMapRequest;
use App\MedicationGroupsMap;
use CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup;
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
        $count = MedicationGroupsMap::destroy($id);

        return (bool) $count ? $this->ok() : $this->error('Unable to delete '.$id);
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
        $medicationGroups = CpmMedicationGroup::all()->sortBy('name')->values()
            ->transform(
                function ($m) {
                    return [
                        'id'   => $m->id,
                        'text' => $m->name,
                    ];
                }
            )
            ->toJson();
        $maps = MedicationGroupsMap::with('cpmMedicationGroup')
            ->get()
            ->sortBy('keyword')
            ->values()
            ->transform(
                function ($m) {
                    return [
                        'id'                  => $m->id,
                        'keyword'             => $m->keyword,
                        'medication_group_id' => $m->medication_group_id,
                        'medication_group'    => $m->cpmMedicationGroup->name,
                    ];
                }
            )
            ->toJson();

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
    public function store(StoreMedicationGroupMapRequest $request)
    {
        $stored = MedicationGroupsMap::create($request->input());
        $stored->load('cpmMedicationGroup');

        return response()->json(
            [
                'stored' => $stored,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(
        Request $request,
        $id
    ) {
    }
}
