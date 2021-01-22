<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\ChunksEloquentBuilderJob;
use CircleLinkHealth\Core\Tests\TestCase;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Queue;

class ChunkIntoJobsTest extends TestCase
{
    /**
     * @return void
     */
    public function test_example()
    {
        factory(User::class, 20)->create();

        Queue::fake();

        Queue::assertNothingPushed();

        $count = User::count();

        $chunkSize = 10;

        User::chunkIntoJobs($chunkSize, new FakeJob());

        Queue::assertPushed(FakeJob::class, (int) ceil($count / $chunkSize));
    }
}

class FakeJob extends ChunksEloquentBuilderJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Builder $builder;

    protected Carbon $chargeableMonth;

    public function getBuilder(): Builder
    {
        return User::offset($this->getOffset())
            ->limit($this->getLimit());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    }
}
