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
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    }

}
