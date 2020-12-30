<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Core\Jobs\EncryptedLaravelJob as Job;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;

class CompareCurrentAndLegacyBillingDataForPractice extends Job
{
    protected array $idsToInvestigate = [];
    protected Carbon $month;
    protected int $practiceId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $practiceId, Carbon $month = null)
    {
        $this->practiceId = $practiceId;
        $this->month      = $month ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    public static function fromParameters(...$parameters)
    {
        $date = isset($parameters[1]) ? Carbon::parse($parameters[1]) : null;

        return new self((int) $parameters[0], $date);
    }

    public function getMonth(): Carbon
    {
        return $this->month;
    }

    public function getPracticeId(): int
    {
        return $this->practiceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        User::ofType('participant')
            ->ofPractice($this->getPracticeId())
            ->whereHas('patientInfo', fn ($pi) => $pi->enrolled())
            ->whereHas('carePlan', fn ($cp) => $cp->whereIn('status', [
                CarePlan::QA_APPROVED,
                CarePlan::RN_APPROVED,
                CarePlan::PROVIDER_APPROVED,
            ]))
            ->chunkIntoJobs(10, new CompareCurrentAndLegacyBillingDataPracticeChunk($this->getPracticeId()));
    }
}
