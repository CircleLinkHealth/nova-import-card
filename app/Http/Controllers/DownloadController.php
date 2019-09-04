<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function downloadMediaFromSignedUrl(Media $media, User $user)
    {
        $practice = $media->model()->firstOrFail();

        if ( ! $user->practice($practice)) {
            //We are not returning 403 to not show potential attackers file exists they just can't access it.
            abort(404);
        }

        if ( ! $media->id) {
        }

        return $this->downloadMedia($media);
    }

    /**
     * Returns file requested to download.
     *
     * @param $filePath
     *
     * @return string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function file($filePath)
    {
        if (empty($filePath)) {
            abort(400, 'File to download must be provided.');
        }

        $path = storage_path($filePath);

        //try looking in the download folder
        if ( ! file_exists($path)) {
            $path = storage_path("download/${filePath}");
        }

        if ( ! file_exists($path)) {
            $path = storage_path("eligibility-templates/${filePath}");
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
            return abort(400, "Could not locate file with name: ${filePath}");
        }

        $fileName = str_replace('/', '', strrchr($filePath, '/'));

        return response()->download(
            $path,
            $fileName,
            [
                'Content-Length: '.filesize($path),
            ]
        );
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

    public function postDownloadfile(Request $request)
    {
        return $this->file($request->input('filePath'));
    }

    private function canDownload(Media $media)
    {
        if (Practice::class != $media->model_type) {
            return true;
        }

        $practiceId = $media->model_id;

        return auth()->user()->practice((int) $practiceId) || auth()->user()->isAdmin();
    }
}
