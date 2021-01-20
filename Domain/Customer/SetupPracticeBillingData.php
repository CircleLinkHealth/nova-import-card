<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use CircleLinkHealth\CcmBilling\Jobs\ClearPracticeLocationSummaries;
use CircleLinkHealth\CcmBilling\Jobs\MigrateChargeableServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Jobs\MigratePracticeServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Jobs\ProcessAllPracticePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Jobs\ProcessPracticePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Jobs\SeedCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\CcmBilling\Jobs\SeedPracticeCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\Location;

class SetupPracticeBillingData
{
    public static function execute()
    {
        MigrateChargeableServicesFromChargeablesToLocationSummariesTable::withChain(
            [
                new SeedCpmProblemChargeableServicesFromLegacyTables(),
                new ProcessAllPracticePatientMonthlyServices(),
            ]
        )->dispatch();
    }

    public static function forPractice(int $practiceId)
    {
        MigratePracticeServicesFromChargeablesToLocationSummariesTable::withChain([
            new SeedPracticeCpmProblemChargeableServicesFromLegacyTables($practiceId),
            new ProcessPracticePatientMonthlyServices($practiceId),
        ])->dispatch($practiceId);
    }

    public static function sync(int $practiceId)
    {
        //todo: revisit clearing location summaries
        ClearPracticeLocationSummaries::withChain([
            new MigratePracticeServicesFromChargeablesToLocationSummariesTable($practiceId),
            new SeedPracticeCpmProblemChargeableServicesFromLegacyTables($practiceId),
            new ProcessPracticePatientMonthlyServices($practiceId),
        ])
            ->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE))
            ->dispatch($practiceId);
    }
}
