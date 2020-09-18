<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatientCallManagementController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
    }

    /**
     * New Calls page with lazy-loading.
     *
     * @return Response
     */
    public function remix(Request $request)
    {
        return view('admin.patientCallManagement.remix');
    }

    /**
     * Newer Calls page with lazy-loading and view table.
     *
     * @return Response
     */
    public function remixV2(Request $request)
    {
        return view('admin.patientCallManagement.remixV2');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
    }
}
