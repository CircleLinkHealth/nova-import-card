<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\CcmBilling\Domain\Customer\SetupPracticeBillingData;
use CircleLinkHealth\Core\Jobs\EncryptedLaravelJob as Job;
use CircleLinkHealth\Customer\Entities\Practice;

class SetupBillingData extends Job
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Practice::activeBillable()->each(fn ($p) => SetupPracticeBillingData::sync($p->id));
    }
}
