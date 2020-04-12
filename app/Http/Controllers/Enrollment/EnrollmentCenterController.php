<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\CareAmbassadorLog;
use App\Http\Controllers\Controller;
use App\Services\Enrollment\AttachEnrolleeFamilyMembers;
use App\Services\Enrollment\EnrollableCallQueue;
use App\TrixField;
use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Jobs\ImportConsentedEnrollees;
use Illuminate\Http\Request;

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
