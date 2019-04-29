<?php

namespace CircleLinkHealth\Customer\Providers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class CustomerServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->bind(DatabaseNotification::class, \CircleLinkHealth\Core\Entities\DatabaseNotification::class);
        $this->app->bind(
            HasDatabaseNotifications::class,
            \CircleLinkHealth\Core\Traits\HasDatabaseNotifications::class
        );
        $this->app->bind(Notifiable::class, \CircleLinkHealth\Core\Traits\Notifiable::class);
    }
    
    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes(
            [
                __DIR__.'/../Config/config.php' => config_path('customer.php'),
            ],
            'config'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'customer'
        );
        
        $this->publishes(
            [
                __DIR__.'/../Config/cerberus.php' => config_path('cerberus.php'),
            ],
            'cerberus'
        );
        
        $this->mergeConfigFrom(
            __DIR__.'/../Config/cerberus.php',
            'cerberus'
        );
    }
    
    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/customer');
        
        $sourcePath = __DIR__.'/../Resources/views';
        
        $this->publishes(
            [
                $sourcePath => $viewPath,
            ],
            'views'
        );
        
        $this->loadViewsFrom(
            array_merge(
                array_map(
                    function ($path) {
                        return $path.'/modules/customer';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'customer'
        );
    }
    
    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/customer');
        
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'customer');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'customer');
        }
    }
    
    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if ( ! app()->environment('production')) {
            app(Factory::class)->load(__DIR__.'/../Database/factories');
        }
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
