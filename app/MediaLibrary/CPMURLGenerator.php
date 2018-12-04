<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\UrlGenerator\S3UrlGenerator;

class CPMURLGenerator extends S3UrlGenerator
{
    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return route('download', ['fileName' => base64_encode(json_encode(['media_id' => $this->media->id]))]);
    }
}
