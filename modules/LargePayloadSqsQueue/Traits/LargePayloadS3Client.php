<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\LargePayloadSqsQueue\Traits;

use CircleLinkHealth\LargePayloadSqsQueue\Constants;
use Illuminate\Support\Facades\Storage;

trait LargePayloadS3Client
{
    private function getS3Client()
    {
        return Storage::drive($this->payloadS3BucketName());
    }

    private function payloadS3BucketName()
    {
        return Constants::S3_BUCKET;
    }
}
