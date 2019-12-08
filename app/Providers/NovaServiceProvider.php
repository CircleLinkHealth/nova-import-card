<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Nova\Metrics\PatientsOverTargetBhiTime;
use App\Nova\Metrics\PatientsOverTargetCcmTime;
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

        \Laravel\Nova\Fields\Field::macro('withModel', function ($model) {
            $this->withMeta(['model' => $model]);

            return $this;
        });

        \Laravel\Nova\Fields\Field::macro('rulesForCard', function ($rules) {
            $this->withMeta(['rulesForCard' => $rules]);

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
        return [];
    }

    /**
     * Get the cards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            new PatientsOverTargetCcmTime(),
            new PatientsOverTargetBhiTime(),
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
