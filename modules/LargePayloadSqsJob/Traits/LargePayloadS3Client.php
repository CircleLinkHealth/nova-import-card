<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\LargePayloadSqsJob\Traits;

use CircleLinkHealth\LargePayloadSqsJob\Constants;
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
