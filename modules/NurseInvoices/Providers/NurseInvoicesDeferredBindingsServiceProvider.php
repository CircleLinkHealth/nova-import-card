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
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class NurseInvoicesDeferredBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
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
        $this->commands([
            GenerateMonthlyInvoicesForNonDemoNurses::class,
            SendMonthlyNurseInvoiceFAN::class,
            SendMonthlyNurseInvoiceLAN::class,
            SendResolveInvoiceDisputeReminder::class,
            ManualInvoiceDownloadCommand::class,
        ]);
    }
}
