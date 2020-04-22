<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\MediaLibrary;

use Spatie\MediaLibrary\UrlGenerator\S3UrlGenerator;

class CPMURLGenerator extends S3UrlGenerator
{
    /**
     * Get the url for the profile of a media item.
     */
    public function getUrl(): string
    {
        return route('download', ['filePath' => base64_encode(json_encode(['media_id' => $this->media->id]))]);
    }
}
