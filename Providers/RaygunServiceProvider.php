<?php

namespace CircleLinkHealth\Raygun\Providers;

use CircleLinkHealth\Raygun\LaravelLogger;
use CircleLinkHealth\Raygun\Facades\Raygun;
use CircleLinkHealth\Raygun\MultiLogger;
use CircleLinkHealth\Raygun\PsrLogger\MultiLogger as BaseMultiLogger;
use CircleLinkHealth\Raygun\PsrLogger\RaygunLogger;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Log\LogManager;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Raygun4php\RaygunClient;
use Illuminate\Contracts\Logging\Log;
use Psr\Log\LoggerInterface;


class RaygunServiceProvider extends ServiceProvider
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
        $this->registerConfig();
        
        $this->app->register(RouteServiceProvider::class);
        
        if (true === Config::get('cpm-module-raygun.enable_crash_reporting')) {
            $this->app->singleton(
                'raygun',
                function ($app) {
                    $client = new RaygunClient(
                        Config::get('cpm-module-raygun.apiKey'),
                        $this->shouldEnableAsync(),
                        Config::get('cpm-module-raygun.debugMode')
                    );
                    
                    if (\Auth::check()) {
                        $authUSer = \Auth::user();
                        
                        $client->SetUser(
                            $user = $authUSer->id,
                            $firstName = $authUSer->first_name,
                            $fullName = $authUSer->display_name,
                            $email = $authUSer->email
                        );
                    }
                    
                    return $client;
                }
            );
    
            $this->app->singleton(
                'raygun.logger', function (Container $app) {
                $config = $app->config->get('cpm-module-raygun');
                $logger = interface_exists(Log::class) ? new LaravelLogger($app['raygun'], $app['events']) : new RaygunLogger($app['raygun']);
                if (isset($config['logger_notify_level'])) {
                    $logger->setNotifyLevel($config['logger_notify_level']);
                }
        
                return $logger;
            });
    
            $this->app->singleton('raygun.multi', function (Container $app) {
                return interface_exists(Log::class) ? new MultiLogger([$app['log'], $app['raygun.logger']]) : new BaseMultiLogger([$app['log'], $app['raygun.logger']]);
            });
    
            if ($this->app['log'] instanceof LogManager) {
                $this->app['log']->extend('raygun', function (Container $app, array $config) {
                    $handler = new PsrHandler($app['raygun.logger']);
            
                    return new Logger('raygun', [$handler]);
                });
            }
            $this->app->alias('raygun.logger', interface_exists(Log::class) ? LaravelLogger::class : RaygunLogger::class);
            $this->app->alias('raygun.multi', interface_exists(Log::class) ? MultiLogger::class : BaseMultiLogger::class);
    
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Raygun', Raygun::class);
    
            $this->app->alias('raygun.logger', Log::class);
            $this->app->alias('raygun.logger', LoggerInterface::class);
        }
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
                __DIR__.'/../Config/config.php' => config_path('cpm-module-raygun.php'),
            ],
            'config'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'cpm-module-raygun'
        );
    }
    
    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/raygun');
        
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
                        return $path.'/modules/raygun';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'raygun'
        );
    }
    
    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/raygun');
        
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'raygun');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'raygun');
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
    
    /**
     * Checks whether the given function name is available.
     *
     * @param string $func the function name
     *
     * @return bool
     */
    public static function functionAvailable($func)
    {
        $disabled = explode(',', ini_get('disable_functions'));
        
        return function_exists($func) && ! in_array($func, $disabled);
    }
    
    private function shouldEnableAsync()
    {
        return true === Config::get('laravel-raygun.async') && self::functionAvailable('exec');
    }
}
