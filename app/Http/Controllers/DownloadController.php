<?php

namespace App\Http\Controllers;

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
            $filePath = base64_decode($filePath);
            $path = storage_path($filePath);
        }

        if (!file_exists($path)) {
            return "Could not locate file with name: $filePath";
        }

        return response()->download($path, $filePath, [
            'Content-Length: ' . filesize($path),
        ]);
    }
}
