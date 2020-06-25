<?php

namespace App\Http\Controllers;

use CircleLinkHealth\Core\Jobs\ProcessPostmarkMailStatusCallbackJob;
use Illuminate\Http\Request;

class PostmarkController extends Controller
{
    public function statusCallback(Request $request)
    {
        ProcessPostmarkMailStatusCallbackJob::dispatch($request->all());
    
        return response()->json([]);
    }
}
