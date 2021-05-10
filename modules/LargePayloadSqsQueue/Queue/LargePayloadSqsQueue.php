<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\LargePayloadSqsQueue\Queue;

use CircleLinkHealth\LargePayloadSqsQueue\Jobs\LargePayloadJob;
use Illuminate\Queue\SqsQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function tap;

class LargePayloadSqsQueue extends SqsQueue
{
    /**
     * The maximum size that SQS can accept.
     */
    const MAX_SQS_SIZE_KB = 256;

    /**
     * {@inheritdoc}
     */
    public function isTooBig($message, $max_size = null)
    {
        $max_size = $max_size
            ?: static::MAX_SQS_SIZE_KB;

        return strlen($message) > $max_size * 1024;
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $key = $this->storePayloadInS3($payload);
        $bucket = $this->payloadS3BucketName();

        $s3Pointer = $this->createPayload(new LargePayloadJob($key, $bucket));
        
        json_encode([
            's3BucketName' => $bucket,
            's3Key'        => $key,
        ]);

        return parent::pushRaw($s3Pointer, $queue, $options);
    }

    /**
     * Generate a UUID v4.
     *
     * @return string
     *                The uuid
     */
    protected function generateUuid()
    {
        return Str::uuid()->toString();
    }

    private function getS3Client()
    {
        return Storage::drive($this->payloadS3BucketName());
    }

    private function payloadS3BucketName()
    {
        return 'media';
    }
    
    /**
     * Stores the payload in S3 and returns the S3 key
     *
     * @param $payload
     *
     * @return string
     */
    private function storePayloadInS3($payload):string
    {
        return tap($this->generateUuid().'.json', function ($key) use ($payload){
            $this->getS3Client()->put(
                $key,
                $payload
            );
        });
    }
}
