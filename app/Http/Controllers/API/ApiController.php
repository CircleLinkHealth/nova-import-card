<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

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
    public function badRequest($message = null)
    {
        return response()->json([
            'message' => $message ?? 'invalid request',
        ], 400);
    }

    public function conflict($message = null)
    {
        return response()->json([
            'message' => $message ?? 'there was a conflict',
        ], 409);
    }

    public function error($message = null, Exception $ex = null)
    {
        return response()->json([
            'message'   => $message ?? 'an error occurred',
            'exception' => $ex,
        ], 500);
    }

    public function json($data)
    {
        return response()->json($data);
    }

    public function notFound($message = null)
    {
        return response()->json([
            'message' => $message ?? 'not found',
        ], 404);
    }

    public function ok($data = null)
    {
        return response()->json($data ?? [
            'message' => 'success',
        ]);
    }
}
