<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Providers;

use CircleLinkHealth\NurseInvoices\AggregatedTotalTimePerNurse;
use CircleLinkHealth\NurseInvoices\Console\Commands\GenerateMonthlyInvoicesForNonDemoNurses;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendMonthlyNurseInvoiceLAN;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendResolveInvoiceDisputeReminder;
use CircleLinkHealth\NurseInvoices\Console\SendMonthlyNurseInvoiceFAN;
use CircleLinkHealth\NurseInvoices\TotalTimeAggregator;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class NurseInvoicesServiceProvider extends ServiceProvider
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
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->bind(AggregatedTotalTimePerNurse::class, function ($app, array $args) {
            $userIds = $args[0];
            $startDate = $args[1];
            $endDate = $args[2];

            return new AggregatedTotalTimePerNurse(new TotalTimeAggregator(parseIds($userIds), $startDate, $endDate));
        });

        $this->commands([
            GenerateMonthlyInvoicesForNonDemoNurses::class,
            SendMonthlyNurseInvoiceFAN::class,
            SendMonthlyNurseInvoiceLAN::class,
            SendResolveInvoiceDisputeReminder::class,
        ]);
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
        $langPath = resource_path('lang/modules/nurseinvoices');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'nurseinvoices');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'nurseinvoices');
        }
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/nurseinvoices');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/nurseinvoices';
        }, \Config::get('view.paths')), [$sourcePath]), 'nurseinvoices');
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('nurseinvoices.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'nurseinvoices'
        );
    }
}
