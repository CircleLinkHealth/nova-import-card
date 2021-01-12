<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

class EnrollmentCenterController extends Controller
{
    public function dashboard()
    {
        //naive authentication for the CPM Caller Service
        $cpmToken = \Hash::make(config('app.key').Carbon::today()->toDateString());

        return view('enrollment-ui.dashboard', compact('cpmToken'));
    }

    public function training()
    {
        return view('enrollment-ui.training');
    }
}
