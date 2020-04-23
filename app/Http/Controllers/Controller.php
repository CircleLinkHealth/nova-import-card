<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\Customer\Entities\Media;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

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

    public function downloadMedia(Media $media)
    {
        return  \Storage::disk('media')
            ->download("{$media->id}/{$media->file_name}");
    }

    public function error($message = null, \Exception $ex = null)
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
