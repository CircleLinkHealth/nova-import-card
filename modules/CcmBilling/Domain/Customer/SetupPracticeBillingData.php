<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\CcmBilling\Jobs\ClearPracticeLocationSummaries;
use CircleLinkHealth\CcmBilling\Jobs\MigrateChargeableServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Jobs\MigratePracticeServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Jobs\ProcessAllPracticePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Jobs\ProcessPracticePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Jobs\SeedCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\CcmBilling\Jobs\SeedPracticeCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\Customer\CpmConstants;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Support\Facades\Bus;

class SetupPracticeBillingData
{
    public static function execute()
    {
        $jobs = [
            new MigrateChargeableServicesFromChargeablesToLocationSummariesTable(),
        ];

        if (Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG)){
            $jobs = new SeedCpmProblemChargeableServicesFromLegacyTables();
        }

        $jobs[] =  new ProcessAllPracticePatientMonthlyServices();

        Bus::chain($jobs)
           ->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE))
           ->dispatch();
    }

    public static function forPractice(int $practiceId)
    {
        $jobs = [
            new MigratePracticeServicesFromChargeablesToLocationSummariesTable($practiceId),
        ];

        if (Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG)){
            $jobs = new SeedPracticeCpmProblemChargeableServicesFromLegacyTables($practiceId);
        }

        $jobs[] = new ProcessPracticePatientMonthlyServices($practiceId);
        Bus::chain($jobs)
           ->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE))
           ->dispatch();
    }

    public static function sync(int $practiceId)
    {
        $jobs = [
            new ClearPracticeLocationSummaries($practiceId),
            new MigratePracticeServicesFromChargeablesToLocationSummariesTable($practiceId),
        ];

        if (Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG)){
            $jobs = new SeedPracticeCpmProblemChargeableServicesFromLegacyTables($practiceId);
        }

        $jobs[] = new ProcessPracticePatientMonthlyServices($practiceId);
        Bus::chain($jobs)
           ->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE))
           ->dispatch();

    }
}
