<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Core\Jobs\EncryptedLaravelJob as Job;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

class CompareCurrentAndLegacyBillingData extends Job implements ShouldBeEncrypted
{
    protected Carbon $month;

    protected array $practiceIds = [];
    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $month = null, array $practiceIds = [])
    {
        $this->month = $month ?? Carbon::now()->startOfMonth()->startOfDay();
        $this->practiceIds = $practiceIds;
    }

    public static function fromParameters(...$parameters)
    {
        $date = isset($parameters[0]) ? Carbon::parse($parameters[0]) : null;

        $practiceDelimitedIds = !isset($parameters[1]) ? [] : explode(',', $parameters[1]);

        return new self($date, $practiceDelimitedIds);
    }

    public function getMonth(): Carbon
    {
        return $this->month;
    }

    public function getPracticeIds():array
    {
        return $this->practiceIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty($this->getPracticeIds())){
            $query = Practice::whereIn('id', $this->getPracticeIds());
        }else {
            $query = Practice::activeBillable();
        }

        $query->each(fn (Practice $p) => CompareCurrentAndLegacyBillingDataForPractice::dispatch($p->id, $this->getMonth()));
    }
}
