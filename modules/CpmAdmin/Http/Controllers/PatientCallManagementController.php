<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
        return view('cpm-admin::admin.patientCallManagement.remix');
    }

    /**
     * Newer Calls page with lazy-loading and view table.
     *
     * @return Response
     */
    public function remixV2(Request $request)
    {
        return view('cpm-admin::admin.patientCallManagement.remixV2');
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