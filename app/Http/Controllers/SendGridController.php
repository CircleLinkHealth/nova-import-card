<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\Core\Jobs\ProcessSendGridMailStatusCallbackJob;
use Illuminate\Http\Request;

class SendGridController extends Controller
{
    public function statusCallback(Request $request)
    {
        ProcessSendGridMailStatusCallbackJob::dispatch($request->all());

        return response()->json([]);
    }
}
