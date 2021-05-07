<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use CircleLinkHealth\Core\ChunksEloquentBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapThree();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
