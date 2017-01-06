<?php

namespace App\Http\Controllers;

class DownloadController extends Controller
{
    /**
     * Returns file requested to download.
     *
     * @param $fileName
     *
     * @return string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function file($fileName)
    {
        $path = storage_path("download/$fileName");

        if (!file_exists($path)) {
            return "Could not locate file with name: $fileName";
        }

        return response()->download($path, $fileName, [
            'Content-Length: ' . filesize($path),
        ]);
    }
}
