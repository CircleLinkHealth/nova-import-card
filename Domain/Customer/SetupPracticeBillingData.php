<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Jobs\MigrateChargeableServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Jobs\MigratePracticeServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Jobs\ProcessAllPracticePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Jobs\ProcessPracticePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Jobs\SeedCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\CcmBilling\Jobs\SeedPracticeCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\Customer\Entities\Location;

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

    public static function sync(int $practiceiD)
    {
        $locations = Location::where('practice_id', $practiceiD)
            ->get();

        ChargeableLocationMonthlySummary::whereIn('location_id', $locations->pluck('id')->toArray())
            ->where('chargeable_month', Carbon::now()->startOfMonth())
            ->delete();

        self::forPractice($practiceiD);
    }
}
