<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeTrackerController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.timeTracker.index');
    }
}
