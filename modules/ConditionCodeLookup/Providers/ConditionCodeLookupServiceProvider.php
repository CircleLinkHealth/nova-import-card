<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ConditionCodeLookup\Providers;

use CircleLinkHealth\ConditionCodeLookup\ConditionCodeLookup;
use CircleLinkHealth\ConditionCodeLookup\Console\Commands\LookupCondition;
use CircleLinkHealth\ConditionCodeLookup\Services\ConditionCodeLookupService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ConditionCodeLookupServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        ConditionCodeLookup::class => ConditionCodeLookupService::class,
    ];

    public function provides()
    {
        return [
            LookupCondition::class,
            ConditionCodeLookup::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $path = getcwd().'/Modules/ConditionCodeLookup/Database/Migrations';
        $this->loadMigrationsFrom($path);

        $this->registerConfig();
        $this->commands([
            LookupCondition::class,
        ]);
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('condition-code-lookup.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'condition-code-lookup'
        );
    }
}
