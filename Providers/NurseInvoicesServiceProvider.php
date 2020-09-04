<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Providers;

use CircleLinkHealth\NurseInvoices\Console\Commands\GenerateMonthlyInvoicesForNonDemoNurses;
use CircleLinkHealth\NurseInvoices\Console\Commands\ManualInvoiceDownloadCommand;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendMonthlyNurseInvoiceLAN;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendResolveInvoiceDisputeReminder;
use CircleLinkHealth\NurseInvoices\Console\SendMonthlyNurseInvoiceFAN;
use Illuminate\Support\ServiceProvider;

class NurseInvoicesServiceProvider extends ServiceProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            GenerateMonthlyInvoicesForNonDemoNurses::class,
            SendMonthlyNurseInvoiceFAN::class,
            SendMonthlyNurseInvoiceLAN::class,
            SendResolveInvoiceDisputeReminder::class,
            ManualInvoiceDownloadCommand::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        
        $this->app->register(RouteServiceProvider::class);
    
        $this->commands(
            [
                GenerateMonthlyInvoicesForNonDemoNurses::class,
                SendMonthlyNurseInvoiceFAN::class,
                SendMonthlyNurseInvoiceLAN::class,
                SendResolveInvoiceDisputeReminder::class,
                ManualInvoiceDownloadCommand::class,
            ]
        );
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/nurseinvoices');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes(
            [
                $sourcePath => $viewPath,
            ],
            'views'
        );

        $this->loadViewsFrom(
            array_merge(
                array_map(
                    function ($path) {
                        return $path.'/modules/nurseinvoices';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'nurseinvoices'
        );
    }
}
