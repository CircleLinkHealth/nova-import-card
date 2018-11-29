<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Practice;
use Spatie\MediaLibrary\Models\Media;

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
        if ( ! file_exists($path)) {
            $path = storage_path("download/$filePath");
        }

        if ( ! file_exists($path)) {
            $path = storage_path("eligibility-templates/$filePath");
        }

        if ( ! file_exists($path)) {
            $downloadMedia = $this->mediaFileExists($filePath);

            if ($downloadMedia) {
                return $downloadMedia;
            }

            $path = storage_path($filePath);
        }

        if ( ! file_exists($path)) {
            $path = $filePath;
        }

        if ( ! file_exists($path)) {
            $path = base64_decode($filePath);
        }

        if ( ! file_exists($path)) {
            return "Could not locate file with name: $filePath";
        }

        $fileName = str_replace('/', '', strrchr($filePath, '/'));

        return response()->download($path, $fileName, [
            'Content-Length: ' . filesize($path),
        ]);
    }

    public function postDownloadfile(Request $request)
    {
        return $this->file($request->input('filePath'));
    }

    public function mediaFileExists($filePath)
    {
        $filePath = base64_decode($filePath);

        if (is_json($filePath)) {
            $decoded = json_decode($filePath, true);

            if ( ! empty($decoded['media_id'])) {
                $media = Media::findOrFail($decoded['media_id']);

                if ( ! $this->canDownload($media)) {
                    abort(403);
                }

                return $this->downloadMedia($media);
            }
        }

        return null;
    }

    private function canDownload(Media $media)
    {
        if ($media->model_type != Practice::class) {
            return true;
        }

        $practiceId = $media->model_id;

        return auth()->user()->practice((int)$practiceId) || auth()->user()->isAdmin();
    }
}
