<?php

namespace App\Http\Controllers;

use Spatie\MediaLibrary\Media;

class DownloadController extends Controller
{
    /**
     * Returns file requested to download.
     *
     * @param $filePath
     *
     * @return string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function file($filePath)
    {
        $path = storage_path($filePath);

        //try looking in the download folder
        if (!file_exists($path)) {
            $path = storage_path("download/$filePath");
        }

        if (!file_exists($path)) {
            $filePath = trim(base64_decode($filePath));

            if (is_json($filePath)) {
                $decoded = json_decode($filePath, true);

                if (!empty($decoded['media_id'])) {
                    $media = Media::findOrFail($decoded['media_id']);

                    return \Storage::disk('media')
                        ->download("{$media->id}/{$media->file_name}");
                }
            }

            $path = storage_path($filePath);
        }

        if (!file_exists($path)) {
            $path = $filePath;
        }

        if (!file_exists($path)) {
            return "Could not locate file with name: $filePath";
        }

        $fileName = str_replace('/', '', strrchr($filePath, '/'));

        return response()->download($path, $fileName, [
            'Content-Length: ' . filesize($path),
        ]);
    }
}
