<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\Core\Jobs\EncryptedLaravelJob as Job;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

class ClearPracticeLocationSummaries extends Job implements ShouldBeEncrypted
{
    protected Carbon $month;

    protected int $practiceId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $practiceId, Carbon $month = null)
    {
        $this->practiceId = $practiceId;
        $this->month      = $month ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    public static function fromParameters(string ...$parameters)
    {
        $date = isset($parameters[1]) ? Carbon::parse($parameters[1]) : null;

        return new static((int) $parameters[0], $date);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $locations = Location::where('practice_id', $this->practiceId)
            ->get();

        //todo: also check location patients where they have actor_id for that billing status?
        ChargeableLocationMonthlySummary::whereIn('location_id', $locations->pluck('id')->toArray())
            ->where('chargeable_month', $this->month)
            ->where('is_locked', 0)
            ->delete();
    }
}
