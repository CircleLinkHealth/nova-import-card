<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimeTrackerController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.timeTracker.index');
    }
}
