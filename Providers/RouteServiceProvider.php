<?php

namespace CircleLinkHealth\SelfEnrollment\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The root namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $namespace = 'CircleLinkHealth\SelfEnrollment\Http\Controllers';

    /**
     * Bootstrap services.
     *
     * @return void
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

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('Routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(__DIR__.'/../Routes/api.php');
    }
}
