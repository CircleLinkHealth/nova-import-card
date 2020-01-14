<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\CPM\CpmMiscService;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;

class MiscController extends Controller
{
    private $miscService;

    /**
     * MedicationController constructor.
     */
    public function __construct(CpmMiscService $miscService)
    {
        $this->miscService = $miscService;
    }

    /**
     * returns a list of paginated Medication in the system.
     */
    public function index()
    {
        return response()->json(CpmMisc::get());
    }

    public function patients($id)
    {
        return response()->json($this->miscService->miscPatients($id));
    }

    public function show($id)
    {
        return response()->json(CpmMisc::find($id));
    }

    public function test()
    {
        return response()->json([
            'message' => 'clh',
        ]);
    }
}
