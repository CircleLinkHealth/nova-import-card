<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor\Queue;

use CircleLinkHealth\LargePayloadSqsJob\Constants;
use CircleLinkHealth\LargePayloadSqsJob\Jobs\DeletePayloadFromS3;
use CircleLinkHealth\LargePayloadSqsJob\Jobs\JobWithLargePayload;
use CircleLinkHealth\LargePayloadSqsJob\Traits\LargePayloadS3Client;
use Illuminate\Queue\SqsQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VaporQueue extends SqsQueue
{
    use LargePayloadS3Client;

    /**
     * Pop the next job off of the queue.
     *
     * @param  string                               $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $response = $this->sqs->receiveMessage([
            'QueueUrl'       => $queue = $this->getQueue($queue),
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        if ( ! is_null($response['Messages']) && count($response['Messages']) > 0) {
            return new VaporJob(
                $this->container,
                $this->sqs,
                $response['Messages'][0],
                $this->connectionName,
                $queue
            );
        }
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $jobName = json_decode($payload)->displayName;

        if ( ! $this->payloadIsTooLarge($payload)) {
            Log::debug("Dispatching $jobName as the payload is within allowed size");

            return parent::pushRaw($payload, $queue, $options);
        }

        $key = $this->storeLargeJobInS3($payload);

        $bucket = $this->payloadS3BucketName();

        Log::debug("Dispatching JobWithLargePayload[{$bucket}][{$key}] instead of {$jobName}");

        $jobWithLargePayload = $this->createPayload(
            Bus::chain([
                           new JobWithLargePayload($key, $bucket),
                           new DeletePayloadFromS3($key, $bucket),
                       ])
            ,
            $queue, [
            's3BucketName' => $bucket,
            's3Key'        => $key,
        ]);

        return parent::pushRaw($jobWithLargePayload, $queue, $options);
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param  string $job
     * @param  string $queue
     * @param  mixed  $data
     * @return array
     */
    protected function createPayloadArray($job, $queue, $data = '')
    {
        return array_merge(parent::createPayloadArray($job, $queue, $data), [
            'attempts' => 0,
        ]);
    }

    protected function generateUuid()
    {
        return Str::uuid()->toString();
    }

    private function payloadIsTooLarge($payload): bool
    {
        return strlen(json_encode($payload)) > Constants::MAX_SQS_SIZE_BYTES;
    }

    /**
     * Stores the payload in S3 and returns the S3 key.
     *
     * @param $job
     */
    private function storeLargeJobInS3($job): string
    {
        return tap($this->generateUuid().'.json', function ($key) use ($job) {
            $this->getS3Client()->put(
                $key,
                $job
            );
        });
    }
}
