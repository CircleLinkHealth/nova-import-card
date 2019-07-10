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
            AggregatedTotalTimePerNurse::class,
            GenerateMonthlyInvoicesForNonDemoNurses::class,
            SendMonthlyNurseInvoiceFAN::class,
            SendMonthlyNurseInvoiceLAN::class,
            SendResolveInvoiceDisputeReminder::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->bind(AggregatedTotalTimePerNurse::class, function ($app, array $args) {
            $userIds = $args[0];
            $startDate = $args[1];
            $endDate = $args[2];

            return new AggregatedTotalTimePerNurse(new TotalTimeAggregator(parseIds($userIds), $startDate, $endDate));
        });

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    GenerateMonthlyInvoicesForNonDemoNurses::class,
                    SendMonthlyNurseInvoiceFAN::class,
                    SendMonthlyNurseInvoiceLAN::class,
                    SendResolveInvoiceDisputeReminder::class,
                ]
            );
        }
    }
}
