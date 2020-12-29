<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\LogPatientCcmStatusForEndOfMonth;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateEndOfMonthCcmStatusLogsChunk extends ChunksEloquentBuilderJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Carbon $month;

    public function __construct(Carbon $month = null)
    {
        $this->month = $month ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    public function getBuilder(): Builder
    {
        return User::ofType('participant')
            ->has('patientInfo')
            ->with('patientInfo')
            ->offset($this->getOffset())
            ->limit($this->getLimit());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getBuilder()->each(function (User $patient) {
            LogPatientCcmStatusForEndOfMonth::create($patient->id, $patient->getCcmStatus(), $this->month);
        });
    }
}
