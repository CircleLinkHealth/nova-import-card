<?php


namespace CircleLinkHealth\LargePayloadSqsQueue;


use CircleLinkHealth\LargePayloadSqsQueue\Jobs\LargePayloadJob;
use CircleLinkHealth\LargePayloadSqsQueue\Traits\LargePayloadS3Client;
use Illuminate\Support\Str;

class LargeJobsDispatcherInterceptor
{
    use LargePayloadS3Client;
    
    public function handle($job) {
        if (! $this->payloadIsTooLarge($job->payload())) {
            return;
        }
    
        $key = $this->storeLargeJobInS3($job);
        $job->delete();
        
        LargePayloadJob::dispatch($key, $this->payloadS3BucketName());
    }
    
    private function payloadIsTooLarge(array $payload) :bool {
        return strlen(json_encode($payload)) > Constants::MAX_SQS_SIZE_BYTES;
    }
    
    /**
     * Stores the payload in S3 and returns the S3 key
     *
     * @param $job
     *
     * @return string
     */
    private function storeLargeJobInS3($job):string
    {
        return tap($this->generateUuid().'.json', function ($key) use ($job){
            $this->getS3Client()->put(
                $key,
                serialize($job)
            );
        });
    }
    
    protected function generateUuid()
    {
        return Str::uuid()->toString();
    }
}