<?php

namespace Circlelinkhealth\ClhNovaTheme;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::theme(asset('/circlelinkhealth/clh-nova-theme/theme.css'));

        $this->publishes([
            __DIR__.'/../resources/css' => public_path('circlelinkhealth/clh-nova-theme'),
        ], 'public');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
