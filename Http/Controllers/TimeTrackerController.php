<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TimeTrackerController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.timeTracker.index');
    }
}
