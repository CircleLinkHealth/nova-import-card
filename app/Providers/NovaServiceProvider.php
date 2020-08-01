<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Anaseqal\NovaImport\NovaImport;
use App\Nova\Dashboards\ServerInsights;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        parent::boot();

        /*
         * Nova Assumes UTC in Eloquent. Below makes it show the time as we store it in the DB (EST).
         *
         * @see https://github.com/laravel/framework/issues/19737
         */
        Nova::userTimezone(function () {
            return '+00:00';
        });

        \Laravel\Nova\Fields\Field::macro('withModel', function ($model, $modelKey = null) {
            $this->withMeta([
                'model'    => $model,
                'modelKey' => $modelKey
                    ?: 'id',
            ]);

            return $this;
        });

        \Laravel\Nova\Fields\Field::macro('inputRules', function ($rules) {
            $this->withMeta(['inputRules' => $rules]);

            return $this;
        });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            new NovaImport(),
        ];
    }

    /**
     * Get the cards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
        ];
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            new ServerInsights(),
        ];
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return $user->isAdmin();
        });
    }

    /**
     * Register the Nova routes.
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }
}
