<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;

class EnrollmentCenterController extends Controller
{
    public function dashboard()
    {
        return view('enrollment-ui.dashboard');
    }

    public function training()
    {
        return view('enrollment-ui.training');
    }
}
