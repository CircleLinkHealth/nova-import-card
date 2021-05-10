<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\LargePayloadSqsQueue\Queue;

use CircleLinkHealth\LargePayloadSqsQueue\Constants;
use CircleLinkHealth\LargePayloadSqsQueue\Jobs\LargePayloadJob;
use CircleLinkHealth\LargePayloadSqsQueue\Traits\LargePayloadS3Client;
use Illuminate\Queue\SqsQueue;
use Illuminate\Support\Str;
use function tap;

class LargePayloadSqsQueue extends SqsQueue
{
    use LargePayloadS3Client;
    
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $key = $this->storePayloadInS3($payload);
        $bucket = $this->payloadS3BucketName();

        $s3Pointer = $this->createPayload(new LargePayloadJob($key, $bucket), $this->getQueueName(), [
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
    
    private function getQueueName()
    {
        $driver = Constants::DRIVER;
        
        return config("queue.connections.$driver.queue");
    }
}
