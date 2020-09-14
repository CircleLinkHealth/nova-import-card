<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Providers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use CircleLinkHealth\SamlSp\Console\RegisterSamlUserMapping;
use CircleLinkHealth\SamlSp\Listeners\SamlLoginEventListener;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

/**
 * Class SamlServiceProvider.
 */
class SamlSpServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerViews();
        $this->registerConfig();
        $this->registerFactories();
        $this->registerListeners();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->commands([
            RegisterSamlUserMapping::class,
        ]);
    }

    /**
     * Register an additional directory of factories.
     */
    public function registerFactories()
    {
        if ( ! app()->environment('production')) {
            app(Factory::class)->load(__DIR__.'/../Database/factories');
        }
    }

    /**
     * Register translations.
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/samlsp');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'samlsp');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'samlsp');
        }
    }

    /**
     * Register views.
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/samlsp');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/samlsp';
        }, \Config::get('view.paths')), [$sourcePath]), 'samlsp');
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes(
            [
                __DIR__.'/../Config/config.php' => config_path('samlsp.php'),
            ],
            'config'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'samlsp'
        );
    }

    private function addConfig($path, $key)
    {
        if ( ! ($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');

            $config->set($key, array_merge(
                $config->get($key, []),
                require $path
            ));
        }
    }

    private function registerListeners()
    {
        $this->app['events']->listen(Saml2LoginEvent::class, SamlLoginEventListener::class);
    }
}
