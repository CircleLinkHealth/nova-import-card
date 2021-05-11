<?php


namespace CircleLinkHealth\LargePayloadSqsJob;


use CircleLinkHealth\LargePayloadSqsJob\Jobs\JobWithLargePayload;
use CircleLinkHealth\LargePayloadSqsJob\Traits\LargePayloadS3Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LargeJobsDispatcherInterceptor
{
    use LargePayloadS3Client;
    
    public function handle($job) {
        if (! $this->payloadIsTooLarge($job)) {
            return;
        }
        
        $payload = $job->payload();
        $key = $this->storeLargeJobInS3($payload);
        $job->delete();
        
        $bucket = $this->payloadS3BucketName();
    
        Log::debug("Dispatching JobWithLargePayload[{$bucket}][{$key}] instead of {$job->resolveName()}");
    
        JobWithLargePayload::dispatch($key, $bucket);
    }
    
    private function payloadIsTooLarge($payload) :bool {
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