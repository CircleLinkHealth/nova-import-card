<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function notFound($message = null) {
        return response()->json([
            'message' => $message ?? 'not found'
        ], 404);
    }
    
    public function badRequest($message = null) {
        return response()->json([
            'message' => $message ?? 'invalid request'
        ], 400);
    }
    
    public function error($message = null, Exception $ex = null) {
        return response()->json([
            'message' => $message ?? 'an error occurred',
            'exception' => $ex
        ], 500);
    }
}
