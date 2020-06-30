<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Providers;

use CircleLinkHealth\NurseInvoices\Console\Commands\GenerateMonthlyInvoicesForNonDemoNurses;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendMonthlyNurseInvoiceLAN;
use CircleLinkHealth\NurseInvoices\Console\Commands\SendResolveInvoiceDisputeReminder;
use CircleLinkHealth\NurseInvoices\Console\Commands\TestInvoiceDownloadCommand;
use CircleLinkHealth\NurseInvoices\Console\SendMonthlyNurseInvoiceFAN;
use Illuminate\Support\ServiceProvider;

class NurseInvoicesDeferredBindingsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

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
            TestInvoiceDownloadCommand::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    GenerateMonthlyInvoicesForNonDemoNurses::class,
                    SendMonthlyNurseInvoiceFAN::class,
                    SendMonthlyNurseInvoiceLAN::class,
                    SendResolveInvoiceDisputeReminder::class,
                    TestInvoiceDownloadCommand::class,
                ]
            );
        } else {
            $this->commands(
                [
                    GenerateMonthlyInvoicesForNonDemoNurses::class,
                ]
            );
        }
    }
}
