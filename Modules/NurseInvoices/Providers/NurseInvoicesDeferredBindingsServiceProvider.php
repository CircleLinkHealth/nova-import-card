<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Providers;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
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
            if (empty($args)) {
                $userIds = User::ofType('care-center')
                    ->with(
                                   [
                                       'nurseInfo' => function ($info) {
                                           $info->with(
                                               [
                                                   'windows',
                                                   'holidays',
                                                   'workhourables',
                                               ]
                                           );
                                       },
                                   ]
                               )
                    ->whereHas(
                                   'nurseInfo',
                                   function ($info) {
                                       $info->where('status', 'active')
                                           ->when(isProductionEnv(), function ($info) {
                                                $info->where('is_demo', false);
                                            });
                                   }
                               )
                    ->pluck('id')
                    ->all();
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
            } else {
                $userIds = $args[0];
                $startDate = $args[1];
                $endDate = $args[2];
            }

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
