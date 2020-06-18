<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Providers;

use App\Services\AthenaAPI\Calls;
use App\Services\AthenaAPI\Connection;
use CircleLinkHealth\Eligibility\Console\Athena\AutoPullEnrolleesFromAthena;
use CircleLinkHealth\Eligibility\Console\Athena\DetermineTargetPatientEligibility;
use CircleLinkHealth\Eligibility\Console\Athena\FixBatch235;
use CircleLinkHealth\Eligibility\Console\Athena\GetAppointmentsForTomorrowFromAthena;
use CircleLinkHealth\Eligibility\Console\Athena\GetCcds;
use CircleLinkHealth\Eligibility\Console\Athena\GetPatientIdFromAppointments;
use CircleLinkHealth\Eligibility\Console\Athena\GetPatientIdFromLastYearAppointments;
use CircleLinkHealth\Eligibility\Console\Athena\PostPatientCarePlanAsAppointmentNote;
use CircleLinkHealth\Eligibility\Console\Athena\UpdatePracticeAppointments;
use CircleLinkHealth\Eligibility\Console\CreatePCMListForCommonWealth;
use CircleLinkHealth\Eligibility\Console\Make65PlusPatientsEligible;
use CircleLinkHealth\Eligibility\Console\ProcessNextEligibilityBatchChunk;
use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use CircleLinkHealth\Eligibility\Console\ResetAthenaEligibilityBatch;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiConnection;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class EligibilityServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->commands([
            AutoPullEnrolleesFromAthena::class,
            CreatePCMListForCommonWealth::class,
            DetermineTargetPatientEligibility::class,
            FixBatch235::class,
            GetAppointmentsForTomorrowFromAthena::class,
            GetCcds::class,
            GetPatientIdFromAppointments::class,
            GetPatientIdFromLastYearAppointments::class,
            PostPatientCarePlanAsAppointmentNote::class,
            ReimportPatientMedicalRecord::class,
            ResetAthenaEligibilityBatch::class,
            UpdatePracticeAppointments::class,
            Make65PlusPatientsEligible::class,
            ProcessNextEligibilityBatchChunk::class,
        ]);
    }

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
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(AthenaApiImplementation::class, function () {
            return new Calls();
        });

        $this->app->singleton(AthenaApiConnection::class, function () {
            $key = config('services.athena.key');
            $secret = config('services.athena.secret');
            $version = config('services.athena.version');

            return new Connection($version, $key, $secret);
        });
    }

    /**
     * Register an additional directory of factories.
     */
    public function registerFactories()
    {
        if ( ! app()->environment('production')) {
            app(Factory::class)->load(__DIR__.'/../Database/factories');
        }
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/eligibility');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'eligibility');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'eligibility');
        }
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/eligibility');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/eligibility';
        }, \Config::get('view.paths')), [$sourcePath]), 'eligibility');
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('eligibility.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'eligibility'
        );
    }
}
