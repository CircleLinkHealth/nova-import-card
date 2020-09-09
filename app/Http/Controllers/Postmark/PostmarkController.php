<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Postmark;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPostmarkInboundMailJob;
use CircleLinkHealth\Core\Jobs\ProcessPostmarkMailStatusCallbackJob;
use Illuminate\Http\Request;

class PostmarkController extends Controller
{
    /**
     * Route called from Postmark whenever we receive an email.
     */
    public function inbound(Request $request)
    {
        ProcessPostmarkInboundMailJob::dispatch($request->all());

        return response()->json([]);
    }

    public function statusCallback(Request $request)
    {
        ProcessPostmarkMailStatusCallbackJob::dispatch($request->all());

        return response()->json([]);
    }
}
