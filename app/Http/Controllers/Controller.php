<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Spatie\MediaLibrary\Models\Media;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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

    public function downloadMedia(Media $media)
    {
        return  \Storage::disk('media')
                        ->download("{$media->id}/{$media->file_name}");
    }
}
