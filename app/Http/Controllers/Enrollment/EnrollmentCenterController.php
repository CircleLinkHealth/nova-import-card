<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\CareAmbassadorLog;
use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\User;

class EnrollmentCenterController extends Controller
{
    public function dashboard()
    {
        /** @var User $user */
        $user    = auth()->user();
        $ccmTime = optional(CareAmbassadorLog::createOrGetLogs($user->careAmbassador->id))->total_time_in_system ?? 0;

        return view('enrollment-ui.dashboard', compact('ccmTime'));
    }

    public function training()
    {
        return view('enrollment-ui.training');
    }
}
