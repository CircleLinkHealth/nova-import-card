<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Providers;

use CircleLinkHealth\Eligibility\Console\Athena\AutoPullEnrolleesFromAthena;
use CircleLinkHealth\Eligibility\Console\Athena\GetAppointmentsForTomorrowFromAthena;
use CircleLinkHealth\Eligibility\Console\Athena\GetCcds;
use CircleLinkHealth\Eligibility\Console\Athena\PostPatientCarePlanAsAppointmentNote;
use CircleLinkHealth\Eligibility\Console\Athena\UpdatePracticeAppointments;
use CircleLinkHealth\Eligibility\Console\Commands\UpdateHealthCenterOfSouthEastTexasLocationNames;
use CircleLinkHealth\Eligibility\Console\CreatePCMListForCommonWealth;
use CircleLinkHealth\Eligibility\Console\Make65PlusPatientsEligible;
use CircleLinkHealth\Eligibility\Console\ProcessNextEligibilityBatchChunk;
use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use CircleLinkHealth\Eligibility\Console\ResetAthenaEligibilityBatch;
use CircleLinkHealth\Eligibility\Console\RestoreEnrolleeProvidersFromRevisions;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiConnection;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Services\AthenaAPI\Calls;
use CircleLinkHealth\Eligibility\Services\AthenaAPI\ConnectionV2;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class EligibilityDeferrableServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'athena.api',
            AthenaApiImplementation::class,
            AthenaApiConnection::class,
            AutoPullEnrolleesFromAthena::class,
            CreatePCMListForCommonWealth::class,
            GetAppointmentsForTomorrowFromAthena::class,
            GetCcds::class,
            PostPatientCarePlanAsAppointmentNote::class,
            ReimportPatientMedicalRecord::class,
            ResetAthenaEligibilityBatch::class,
            UpdatePracticeAppointments::class,
            Make65PlusPatientsEligible::class,
            ProcessNextEligibilityBatchChunk::class,
            RestoreEnrolleeProvidersFromRevisions::class,
            UpdateHealthCenterOfSouthEastTexasLocationNames::class
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->commands([
            AutoPullEnrolleesFromAthena::class,
            CreatePCMListForCommonWealth::class,
            GetAppointmentsForTomorrowFromAthena::class,
            GetCcds::class,
            PostPatientCarePlanAsAppointmentNote::class,
            ReimportPatientMedicalRecord::class,
            ResetAthenaEligibilityBatch::class,
            UpdatePracticeAppointments::class,
            Make65PlusPatientsEligible::class,
            ProcessNextEligibilityBatchChunk::class,
            RestoreEnrolleeProvidersFromRevisions::class,
            UpdateHealthCenterOfSouthEastTexasLocationNames::class
        ]);

        $this->app->singleton(AthenaApiImplementation::class, function () {
            return new Calls();
        });

        $this->app->singleton(AthenaApiConnection::class, function () {
            $activeVersion = config('services.athena.active_version');

            $prefix = "services.athena.$activeVersion";

            $key = config("$prefix.key");
            $secret = config("$prefix.secret");
            $version = config("$prefix.version");

            return new ConnectionV2($version, $key, $secret);
        });
    }
}
