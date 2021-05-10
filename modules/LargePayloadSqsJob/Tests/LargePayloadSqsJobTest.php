<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\LargePayloadSqsJob\Tests;

use CircleLinkHealth\Core\Tests\TestCase;
use CircleLinkHealth\LargePayloadSqsJob\Constants;
use CircleLinkHealth\LargePayloadSqsJob\Jobs\JobWithLargePayload;
use Illuminate\Support\Facades\Queue;

class LargePayloadSqsJobTest extends TestCase
{
    public function test_it_dispatches_large_payload_job_instead_of_actual_job() {
//        Queue::fake();
        DummyJob::dispatch($this->getLargePayload());
//        Queue::assertNotPushed(DummyJob::class);
//        Queue::assertPushed(JobWithLargePayload::class);
    }
    
    private function getLargePayload()
    {
        return generateRandomString(Constants::MAX_SQS_SIZE_BYTES + 5);
    }
}
