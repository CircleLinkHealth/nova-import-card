<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use CircleLinkHealth\SharedModels\Entities\CareAmbassadorLog;
use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Carbon;

class EnrollmentCenterController extends Controller
{
    public function dashboard()
    {
        /** @var User $user */
        $user    = auth()->user();
        $ccmTime = optional(CareAmbassadorLog::createOrGetLogs($user->careAmbassador->id))->total_time_in_system ?? 0;

        //naive authentication for the CPM Caller Service
        $cpmToken = \Hash::make(config('app.key').Carbon::today()->toDateString());

        return view('enrollment-ui.dashboard', compact('ccmTime', 'cpmToken'));
    }

    public function training()
    {
        return view('enrollment-ui.training');
    }
}
