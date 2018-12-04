<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Mail\SMSReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnrollmentSMSController extends Controller
{
    public function handleIncoming(Request $request)
    {
        Mail::to('mantoniou@circlelinkhealth.com')
            ->send(new SMSReceived($request));

        Log::to($request);
    }
}
