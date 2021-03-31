<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * The root namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $namespace = 'CircleLinkHealth\SmartOnFhirSso\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot()
    {
        $this->setRootControllerNamespace();

        if ($this->routesAreCached()) {
            //routes are all in one file
            //we need to make sure that at least one Service Provider will
            //load from cache (see app/Providers/RouteServiceProvider.php)
            return;
        }
        $this->loadRoutes();
    }

    /**
     * Define the routes for the application.
     */
    public function map()
    {
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::namespace($this->namespace)
            ->group(__DIR__.'/../Routes/web.php');
    }
}
