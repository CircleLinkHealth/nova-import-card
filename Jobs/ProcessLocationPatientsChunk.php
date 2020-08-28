<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use App\Contracts\ChunksEloquentBuilder;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLocationPatientsChunk implements ChunksEloquentBuilder, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected AvailableServiceProcessors $availableServiceProcessors;

    protected Builder $builder;

    protected Carbon $chargeableMonth;

    /**
     * Create a new job instance.
     */
    public function __construct(AvailableServiceProcessors $availableServiceProcessors, Carbon $chargeableMonth)
    {
        $this->availableServiceProcessors = $availableServiceProcessors;
        $this->chargeableMonth            = $chargeableMonth;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->builder->get()->each(function (User $patient) {
            ProcessPatientMonthlyServices::dispatch($patient, $this->availableServiceProcessors);
        });
    }

    public function setBuilder(int $offset, int $limit, Builder $builder): self
    {
        $this->builder = $builder
            ->offset($offset)
            ->limit($limit);

        return $this;
    }
}
