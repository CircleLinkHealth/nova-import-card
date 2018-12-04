<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

/**
* @SWG\Swagger(
*   basePath="/api/",
*   @SWG\Info(
*       title="CPM-API",
*       version="1.0.0"
*   )
* )
*/
class ApiController extends BaseController
{
    public function json($data)
    {
        return response()->json($data);
    }

    public function ok($data = null)
    {
        return response()->json($data ?? [
            'message' => 'success'
        ]);
    }

    public function notFound($message = null)
    {
        return response()->json([
            'message' => $message ?? 'not found'
        ], 404);
    }
    
    public function badRequest($message = null)
    {
        return response()->json([
            'message' => $message ?? 'invalid request'
        ], 400);
    }
    
    public function error($message = null, Exception $ex = null)
    {
        return response()->json([
            'message' => $message ?? 'an error occurred',
            'exception' => $ex
        ], 500);
    }
    
    public function conflict($message = null)
    {
        return response()->json([
            'message' => $message ?? 'there was a conflict'
        ], 409);
    }
}
