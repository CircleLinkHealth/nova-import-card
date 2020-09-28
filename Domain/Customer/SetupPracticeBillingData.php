<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use CircleLinkHealth\CcmBilling\Jobs\MigrateChargeableServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Jobs\MigratePracticeServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Jobs\ProcessAllPracticePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Jobs\ProcessPracticePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Jobs\SeedCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\CcmBilling\Jobs\SeedPracticeCpmProblemChargeableServicesFromLegacyTables;

class SetupPracticeBillingData
{
    public static function execute()
    {
        MigrateChargeableServicesFromChargeablesToLocationSummariesTable::dispatch();
        SeedCpmProblemChargeableServicesFromLegacyTables::dispatch();
        ProcessAllPracticePatientMonthlyServices::dispatch();
    }

    public static function forPractice(int $practiceId)
    {
        MigratePracticeServicesFromChargeablesToLocationSummariesTable::dispatch($practiceId);
        SeedPracticeCpmProblemChargeableServicesFromLegacyTables::dispatch($practiceId);
        ProcessPracticePatientMonthlyServices::dispatch($practiceId);
    }
}
