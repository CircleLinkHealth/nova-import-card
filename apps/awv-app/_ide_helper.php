<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

/**
 * A helper file for Laravel, to provide autocomplete information to your IDE
 * Generated for Laravel 8.31.0.
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 * @see https://github.com/barryvdh/laravel-ide-helper
 */

namespace Illuminate\Support\Facades {
        /**
         * @see \Illuminate\Contracts\Foundation\Application
         */
        class App
        {
            /**
             * Throw an HttpException with the given data.
             *
             * @param  int                                                           $code
             * @param  string                                                        $message
             * @param  array                                                         $headers
             * @throws \Symfony\Component\HttpKernel\Exception\HttpException
             * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
             * @return void
             * @static
             */
            public static function abort($code, $message = '', $headers = [])
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->abort($code, $message, $headers);
            }

            /**
             * Add new prefix to list of absolute path prefixes.
             *
             * @param  string                             $prefix
             * @return \Illuminate\Foundation\Application
             * @static
             */
            public static function addAbsoluteCachePathPrefix($prefix)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->addAbsoluteCachePathPrefix($prefix);
            }

            /**
             * Add a contextual binding to the container.
             *
             * @param  string          $concrete
             * @param  string          $abstract
             * @param  \Closure|string $implementation
             * @return void
             * @static
             */
            public static function addContextualBinding($concrete, $abstract, $implementation)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->addContextualBinding($concrete, $abstract, $implementation);
            }

            /**
             * Add an array of services to the application's deferred services.
             *
             * @param  array $services
             * @return void
             * @static
             */
            public static function addDeferredServices($services)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->addDeferredServices($services);
            }

            /**
             * Register a callback to run after a bootstrapper.
             *
             * @param  string   $bootstrapper
             * @param  \Closure $callback
             * @return void
             * @static
             */
            public static function afterBootstrapping($bootstrapper, $callback)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->afterBootstrapping($bootstrapper, $callback);
            }

            /**
             * Register a callback to run after loading the environment.
             *
             * @param  \Closure $callback
             * @return void
             * @static
             */
            public static function afterLoadingEnvironment($callback)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->afterLoadingEnvironment($callback);
            }

            /**
             * Register a new after resolving callback for all types.
             *
             * @param  \Closure|string $abstract
             * @param  \Closure|null   $callback
             * @return void
             * @static
             */
            public static function afterResolving($abstract, $callback = null)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->afterResolving($abstract, $callback);
            }

            /**
             * Alias a type to a different name.
             *
             * @param  string          $abstract
             * @param  string          $alias
             * @throws \LogicException
             * @return void
             * @static
             */
            public static function alias($abstract, $alias)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->alias($abstract, $alias);
            }

            /**
             * Get the base path of the Laravel installation.
             *
             * @param  string $path Optionally, a path to append to the base path
             * @return string
             * @static
             */
            public static function basePath($path = '')
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->basePath($path);
            }

            /**
             * Register a callback to run before a bootstrapper.
             *
             * @param  string   $bootstrapper
             * @param  \Closure $callback
             * @return void
             * @static
             */
            public static function beforeBootstrapping($bootstrapper, $callback)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->beforeBootstrapping($bootstrapper, $callback);
            }

            /**
             * Register a new before resolving callback for all types.
             *
             * @param  \Closure|string $abstract
             * @param  \Closure|null   $callback
             * @return void
             * @static
             */
            public static function beforeResolving($abstract, $callback = null)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->beforeResolving($abstract, $callback);
            }

            /**
             * Register a binding with the container.
             *
             * @param  string               $abstract
             * @param  \Closure|string|null $concrete
             * @param  bool                 $shared
             * @return void
             * @static
             */
            public static function bind($abstract, $concrete = null, $shared = false)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->bind($abstract, $concrete, $shared);
            }

            /**
             * Register a binding if it hasn't already been registered.
             *
             * @param  string               $abstract
             * @param  \Closure|string|null $concrete
             * @param  bool                 $shared
             * @return void
             * @static
             */
            public static function bindIf($abstract, $concrete = null, $shared = false)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->bindIf($abstract, $concrete, $shared);
            }

            /**
             * Bind a callback to resolve with Container::call.
             *
             * @param  array|string $method
             * @param  \Closure     $callback
             * @return void
             * @static
             */
            public static function bindMethod($method, $callback)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->bindMethod($method, $callback);
            }

            /**
             * Boot the application's service providers.
             *
             * @return void
             * @static
             */
            public static function boot()
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->boot();
            }

            /**
             * Register a new "booted" listener.
             *
             * @param  callable $callback
             * @return void
             * @static
             */
            public static function booted($callback)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->booted($callback);
            }

            /**
             * Register a new boot listener.
             *
             * @param  callable $callback
             * @return void
             * @static
             */
            public static function booting($callback)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->booting($callback);
            }

            /**
             * Get the path to the bootstrap directory.
             *
             * @param  string $path Optionally, a path to append to the bootstrap path
             * @return string
             * @static
             */
            public static function bootstrapPath($path = '')
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->bootstrapPath($path);
            }

            /**
             * Run the given array of bootstrap classes.
             *
             * @param  string[] $bootstrappers
             * @return void
             * @static
             */
            public static function bootstrapWith($bootstrappers)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->bootstrapWith($bootstrappers);
            }

            /**
             * Determine if the given abstract type has been bound.
             *
             * @param  string $abstract
             * @return bool
             * @static
             */
            public static function bound($abstract)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->bound($abstract);
            }

            /**
             * Instantiate a concrete instance of the given type.
             *
             * @param  \Closure|string                                            $concrete
             * @throws \Illuminate\Contracts\Container\BindingResolutionException
             * @return mixed
             * @static
             */
            public static function build($concrete)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->build($concrete);
            }

            /**
             * Call the given Closure / class@method and inject its dependencies.
             *
             * @param  callable|string                             $callback
             * @param  \Illuminate\Container\array<string,  mixed> $parameters
             * @param  string|null                                 $defaultMethod
             * @throws \InvalidArgumentException
             * @return mixed
             * @static
             */
            public static function call($callback, $parameters = [], $defaultMethod = null)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->call($callback, $parameters, $defaultMethod);
            }

            /**
             * Get the method binding for the given method.
             *
             * @param  string $method
             * @param  mixed  $instance
             * @return mixed
             * @static
             */
            public static function callMethodBinding($method, $instance)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->callMethodBinding($method, $instance);
            }

            /**
             * Get the path to the application configuration files.
             *
             * @param  string $path Optionally, a path to append to the config path
             * @return string
             * @static
             */
            public static function configPath($path = '')
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->configPath($path);
            }

            /**
             * Determine if the application configuration is cached.
             *
             * @return bool
             * @static
             */
            public static function configurationIsCached()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->configurationIsCached();
            }

            /**
             * Get the current application locale.
             *
             * @return string
             * @static
             */
            public static function currentLocale()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->currentLocale();
            }

            /**
             * Get the path to the database directory.
             *
             * @param  string $path Optionally, a path to append to the database path
             * @return string
             * @static
             */
            public static function databasePath($path = '')
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->databasePath($path);
            }

            /**
             * Detect the application's current environment.
             *
             * @param  \Closure $callback
             * @return string
             * @static
             */
            public static function detectEnvironment($callback)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->detectEnvironment($callback);
            }

            /**
             * Get or check the current application environment.
             *
             * @param  array|string $environments
             * @return bool|string
             * @static
             */
            public static function environment(...$environments)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->environment(...$environments);
            }

            /**
             * Get the environment file the application is using.
             *
             * @return string
             * @static
             */
            public static function environmentFile()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->environmentFile();
            }

            /**
             * Get the fully qualified path to the environment file.
             *
             * @return string
             * @static
             */
            public static function environmentFilePath()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->environmentFilePath();
            }

            /**
             * Get the path to the environment file directory.
             *
             * @return string
             * @static
             */
            public static function environmentPath()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->environmentPath();
            }

            /**
             * Determine if the application events are cached.
             *
             * @return bool
             * @static
             */
            public static function eventsAreCached()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->eventsAreCached();
            }

            /**
             * "Extend" an abstract type in the container.
             *
             * @param  string                    $abstract
             * @param  \Closure                  $closure
             * @throws \InvalidArgumentException
             * @return void
             * @static
             */
            public static function extend($abstract, $closure)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->extend($abstract, $closure);
            }

            /**
             * Get a closure to resolve the given type from the container.
             *
             * @param  string   $abstract
             * @return \Closure
             * @static
             */
            public static function factory($abstract)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->factory($abstract);
            }

            /**
             * Flush the container of all bindings and resolved instances.
             *
             * @return void
             * @static
             */
            public static function flush()
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->flush();
            }

            /**
             * Remove all of the extender callbacks for a given type.
             *
             * @param  string $abstract
             * @return void
             * @static
             */
            public static function forgetExtenders($abstract)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->forgetExtenders($abstract);
            }

            /**
             * Remove a resolved instance from the instance cache.
             *
             * @param  string $abstract
             * @return void
             * @static
             */
            public static function forgetInstance($abstract)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->forgetInstance($abstract);
            }

            /**
             * Clear all of the instances from the container.
             *
             * @return void
             * @static
             */
            public static function forgetInstances()
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->forgetInstances();
            }

            /**
             * Finds an entry of the container by its identifier and returns it.
             *
             * @param  string                      $id identifier of the entry to look for
             * @throws NotFoundExceptionInterface  no entry was found for **this** identifier
             * @throws ContainerExceptionInterface error while retrieving the entry
             * @return mixed                       entry
             * @static
             */
            public static function get($id)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->get($id);
            }

            /**
             * Get the alias for an abstract if available.
             *
             * @param  string $abstract
             * @return string
             * @static
             */
            public static function getAlias($abstract)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getAlias($abstract);
            }

            /**
             * Get the container's bindings.
             *
             * @return array
             * @static
             */
            public static function getBindings()
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getBindings();
            }

            /**
             * Get the path to the configuration cache file.
             *
             * @return string
             * @static
             */
            public static function getCachedConfigPath()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getCachedConfigPath();
            }

            /**
             * Get the path to the events cache file.
             *
             * @return string
             * @static
             */
            public static function getCachedEventsPath()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getCachedEventsPath();
            }

            /**
             * Get the path to the cached packages.php file.
             *
             * @return string
             * @static
             */
            public static function getCachedPackagesPath()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getCachedPackagesPath();
            }

            /**
             * Get the path to the routes cache file.
             *
             * @return string
             * @static
             */
            public static function getCachedRoutesPath()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getCachedRoutesPath();
            }

            /**
             * Get the path to the cached services.php file.
             *
             * @return string
             * @static
             */
            public static function getCachedServicesPath()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getCachedServicesPath();
            }

            /**
             * Get the application's deferred services.
             *
             * @return array
             * @static
             */
            public static function getDeferredServices()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getDeferredServices();
            }

            /**
             * Get the current application fallback locale.
             *
             * @return string
             * @static
             */
            public static function getFallbackLocale()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getFallbackLocale();
            }

            /**
             * Get the globally available instance of the container.
             *
             * @return static
             * @static
             */
            public static function getInstance()
            {            //Method inherited from \Illuminate\Container\Container
                return \Illuminate\Foundation\Application::getInstance();
            }

            /**
             * Get the service providers that have been loaded.
             *
             * @return array
             * @static
             */
            public static function getLoadedProviders()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getLoadedProviders();
            }

            /**
             * Get the current application locale.
             *
             * @return string
             * @static
             */
            public static function getLocale()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getLocale();
            }

            /**
             * Get the application namespace.
             *
             * @throws \RuntimeException
             * @return string
             * @static
             */
            public static function getNamespace()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getNamespace();
            }

            /**
             * Get the registered service provider instance if it exists.
             *
             * @param  \Illuminate\Support\ServiceProvider|string $provider
             * @return \Illuminate\Support\ServiceProvider|null
             * @static
             */
            public static function getProvider($provider)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getProvider($provider);
            }

            /**
             * Get the registered service provider instances if any exist.
             *
             * @param  \Illuminate\Support\ServiceProvider|string $provider
             * @return array
             * @static
             */
            public static function getProviders($provider)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->getProviders($provider);
            }

            /**
             * @static
             * @param mixed $request
             * @param mixed $type
             * @param mixed $catch
             */
            public static function handle($request, $type = 1, $catch = true)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->handle($request, $type, $catch);
            }

            /**
             * Returns true if the container can return an entry for the given identifier.
             *
             * Returns false otherwise.
             *
             * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
             * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
             *
             * @param  string $id identifier of the entry to look for
             * @return bool
             * @static
             */
            public static function has($id)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->has($id);
            }

            /**
             * Determine if the application has been bootstrapped before.
             *
             * @return bool
             * @static
             */
            public static function hasBeenBootstrapped()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->hasBeenBootstrapped();
            }

            /**
             * Determine if the container has a method binding.
             *
             * @param  string $method
             * @return bool
             * @static
             */
            public static function hasMethodBinding($method)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->hasMethodBinding($method);
            }

            /**
             * Register an existing instance as shared in the container.
             *
             * @param  string $abstract
             * @param  mixed  $instance
             * @return mixed
             * @static
             */
            public static function instance($abstract, $instance)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->instance($abstract, $instance);
            }

            /**
             * Determine if a given string is an alias.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function isAlias($name)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->isAlias($name);
            }

            /**
             * Determine if the application has booted.
             *
             * @return bool
             * @static
             */
            public static function isBooted()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->isBooted();
            }

            /**
             * Determine if the given service is a deferred service.
             *
             * @param  string $service
             * @return bool
             * @static
             */
            public static function isDeferredService($service)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->isDeferredService($service);
            }

            /**
             * Determine if the application is currently down for maintenance.
             *
             * @return bool
             * @static
             */
            public static function isDownForMaintenance()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->isDownForMaintenance();
            }

            /**
             * Determine if the application is in the local environment.
             *
             * @return bool
             * @static
             */
            public static function isLocal()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->isLocal();
            }

            /**
             * Determine if the application locale is the given locale.
             *
             * @param  string $locale
             * @return bool
             * @static
             */
            public static function isLocale($locale)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->isLocale($locale);
            }

            /**
             * Determine if the application is in the production environment.
             *
             * @return bool
             * @static
             */
            public static function isProduction()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->isProduction();
            }

            /**
             * Determine if a given type is shared.
             *
             * @param  string $abstract
             * @return bool
             * @static
             */
            public static function isShared($abstract)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->isShared($abstract);
            }

            /**
             * Get the path to the language files.
             *
             * @return string
             * @static
             */
            public static function langPath()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->langPath();
            }

            /**
             * Load the provider for a deferred service.
             *
             * @param  string $service
             * @return void
             * @static
             */
            public static function loadDeferredProvider($service)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->loadDeferredProvider($service);
            }

            /**
             * Load and boot all of the remaining deferred providers.
             *
             * @return void
             * @static
             */
            public static function loadDeferredProviders()
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->loadDeferredProviders();
            }

            /**
             * Set the environment file to be loaded during bootstrapping.
             *
             * @param  string                             $file
             * @return \Illuminate\Foundation\Application
             * @static
             */
            public static function loadEnvironmentFrom($file)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->loadEnvironmentFrom($file);
            }

            /**
             * Resolve the given type from the container.
             *
             * @param  string $abstract
             * @param  array  $parameters
             * @return mixed
             * @static
             */
            public static function make($abstract, $parameters = [])
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->make($abstract, $parameters);
            }

            /**
             * An alias function name for make().
             *
             * @param  callable|string                                            $abstract
             * @param  array                                                      $parameters
             * @throws \Illuminate\Contracts\Container\BindingResolutionException
             * @return mixed
             * @static
             */
            public static function makeWith($abstract, $parameters = [])
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->makeWith($abstract, $parameters);
            }

            /**
             * Determine if a given offset exists.
             *
             * @param  string $key
             * @return bool
             * @static
             */
            public static function offsetExists($key)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->offsetExists($key);
            }

            /**
             * Get the value at a given offset.
             *
             * @param  string $key
             * @return mixed
             * @static
             */
            public static function offsetGet($key)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->offsetGet($key);
            }

            /**
             * Set the value at a given offset.
             *
             * @param  string $key
             * @param  mixed  $value
             * @return void
             * @static
             */
            public static function offsetSet($key, $value)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->offsetSet($key, $value);
            }

            /**
             * Unset the value at a given offset.
             *
             * @param  string $key
             * @return void
             * @static
             */
            public static function offsetUnset($key)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->offsetUnset($key);
            }

            /**
             * Get the path to the application "app" directory.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function path($path = '')
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->path($path);
            }

            /**
             * Configure the real-time facade namespace.
             *
             * @param  string $namespace
             * @return void
             * @static
             */
            public static function provideFacades($namespace)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->provideFacades($namespace);
            }

            /**
             * Determine if the given service provider is loaded.
             *
             * @param  string $provider
             * @return bool
             * @static
             */
            public static function providerIsLoaded($provider)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->providerIsLoaded($provider);
            }

            /**
             * Get the path to the public / web directory.
             *
             * @return string
             * @static
             */
            public static function publicPath()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->publicPath();
            }

            /**
             * Bind a new callback to an abstract's rebind event.
             *
             * @param  string   $abstract
             * @param  \Closure $callback
             * @return mixed
             * @static
             */
            public static function rebinding($abstract, $callback)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->rebinding($abstract, $callback);
            }

            /**
             * Refresh an instance on the given target and method.
             *
             * @param  string $abstract
             * @param  mixed  $target
             * @param  string $method
             * @return mixed
             * @static
             */
            public static function refresh($abstract, $target, $method)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->refresh($abstract, $target, $method);
            }

            /**
             * Register a service provider with the application.
             *
             * @param  \Illuminate\Support\ServiceProvider|string $provider
             * @param  bool                                       $force
             * @return \Illuminate\Support\ServiceProvider
             * @static
             */
            public static function register($provider, $force = false)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->register($provider, $force);
            }

            /**
             * Register all of the configured providers.
             *
             * @return void
             * @static
             */
            public static function registerConfiguredProviders()
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->registerConfiguredProviders();
            }

            /**
             * Register the core class aliases in the container.
             *
             * @return void
             * @static
             */
            public static function registerCoreContainerAliases()
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->registerCoreContainerAliases();
            }

            /**
             * Register a deferred provider and service.
             *
             * @param  string      $provider
             * @param  string|null $service
             * @return void
             * @static
             */
            public static function registerDeferredProvider($provider, $service = null)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->registerDeferredProvider($provider, $service);
            }

            /**
             * Determine if the given abstract type has been resolved.
             *
             * @param  string $abstract
             * @return bool
             * @static
             */
            public static function resolved($abstract)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->resolved($abstract);
            }

            /**
             * Resolve a service provider instance from the class name.
             *
             * @param  string                              $provider
             * @return \Illuminate\Support\ServiceProvider
             * @static
             */
            public static function resolveProvider($provider)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->resolveProvider($provider);
            }

            /**
             * Register a new resolving callback.
             *
             * @param  \Closure|string $abstract
             * @param  \Closure|null   $callback
             * @return void
             * @static
             */
            public static function resolving($abstract, $callback = null)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->resolving($abstract, $callback);
            }

            /**
             * Get the path to the resources directory.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function resourcePath($path = '')
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->resourcePath($path);
            }

            /**
             * Determine if the application routes are cached.
             *
             * @return bool
             * @static
             */
            public static function routesAreCached()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->routesAreCached();
            }

            /**
             * Determine if the application is running in the console.
             *
             * @return bool
             * @static
             */
            public static function runningInConsole()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->runningInConsole();
            }

            /**
             * Determine if the application is running unit tests.
             *
             * @return bool
             * @static
             */
            public static function runningUnitTests()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->runningUnitTests();
            }

            /**
             * Set the base path for the application.
             *
             * @param  string                             $basePath
             * @return \Illuminate\Foundation\Application
             * @static
             */
            public static function setBasePath($basePath)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->setBasePath($basePath);
            }

            /**
             * Set the application's deferred services.
             *
             * @param  array $services
             * @return void
             * @static
             */
            public static function setDeferredServices($services)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->setDeferredServices($services);
            }

            /**
             * Set the current application fallback locale.
             *
             * @param  string $fallbackLocale
             * @return void
             * @static
             */
            public static function setFallbackLocale($fallbackLocale)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->setFallbackLocale($fallbackLocale);
            }

            /**
             * Set the shared instance of the container.
             *
             * @param  \Illuminate\Contracts\Container\Container|null   $container
             * @return \Illuminate\Contracts\Container\Container|static
             * @static
             */
            public static function setInstance($container = null)
            {            //Method inherited from \Illuminate\Container\Container
                return \Illuminate\Foundation\Application::setInstance($container);
            }

            /**
             * Set the current application locale.
             *
             * @param  string $locale
             * @return void
             * @static
             */
            public static function setLocale($locale)
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->setLocale($locale);
            }

            /**
             * Determine if middleware has been disabled for the application.
             *
             * @return bool
             * @static
             */
            public static function shouldSkipMiddleware()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->shouldSkipMiddleware();
            }

            /**
             * Register a shared binding in the container.
             *
             * @param  string               $abstract
             * @param  \Closure|string|null $concrete
             * @return void
             * @static
             */
            public static function singleton($abstract, $concrete = null)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->singleton($abstract, $concrete);
            }

            /**
             * Register a shared binding if it hasn't already been registered.
             *
             * @param  string               $abstract
             * @param  \Closure|string|null $concrete
             * @return void
             * @static
             */
            public static function singletonIf($abstract, $concrete = null)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->singletonIf($abstract, $concrete);
            }

            /**
             * Get the path to the storage directory.
             *
             * @return string
             * @static
             */
            public static function storagePath()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->storagePath();
            }

            /**
             * Assign a set of tags to a given binding.
             *
             * @param  array|string $abstracts
             * @param  array|mixed  $tags
             * @return void
             * @static
             */
            public static function tag($abstracts, $tags)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                $instance->tag($abstracts, $tags);
            }

            /**
             * Resolve all of the bindings for a given tag.
             *
             * @param  string                         $tag
             * @return \Illuminate\Container\iterable
             * @static
             */
            public static function tagged($tag)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->tagged($tag);
            }

            /**
             * Terminate the application.
             *
             * @return void
             * @static
             */
            public static function terminate()
            {
                // @var \Illuminate\Foundation\Application $instance
                $instance->terminate();
            }

            /**
             * Register a terminating callback with the application.
             *
             * @param  callable|string                    $callback
             * @return \Illuminate\Foundation\Application
             * @static
             */
            public static function terminating($callback)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->terminating($callback);
            }

            /**
             * Set the application directory.
             *
             * @param  string                             $path
             * @return \Illuminate\Foundation\Application
             * @static
             */
            public static function useAppPath($path)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->useAppPath($path);
            }

            /**
             * Set the database directory.
             *
             * @param  string                             $path
             * @return \Illuminate\Foundation\Application
             * @static
             */
            public static function useDatabasePath($path)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->useDatabasePath($path);
            }

            /**
             * Set the directory for the environment file.
             *
             * @param  string                             $path
             * @return \Illuminate\Foundation\Application
             * @static
             */
            public static function useEnvironmentPath($path)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->useEnvironmentPath($path);
            }

            /**
             * Set the language file directory.
             *
             * @param  string                             $path
             * @return \Illuminate\Foundation\Application
             * @static
             */
            public static function useLangPath($path)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->useLangPath($path);
            }

            /**
             * Set the storage directory.
             *
             * @param  string                             $path
             * @return \Illuminate\Foundation\Application
             * @static
             */
            public static function useStoragePath($path)
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->useStoragePath($path);
            }

            /**
             * Get the version number of the application.
             *
             * @return string
             * @static
             */
            public static function version()
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->version();
            }

            /**
             * Get the path to the views directory.
             *
             * This method returns the first configured path in the array of view paths.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function viewPath($path = '')
            {
                // @var \Illuminate\Foundation\Application $instance
                return $instance->viewPath($path);
            }

            /**
             * Define a contextual binding.
             *
             * @param  array|string                                             $concrete
             * @return \Illuminate\Contracts\Container\ContextualBindingBuilder
             * @static
             */
            public static function when($concrete)
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->when($concrete);
            }

            /**
             * Wrap the given closure such that its dependencies will be injected when executed.
             *
             * @param  \Closure $callback
             * @param  array    $parameters
             * @return \Closure
             * @static
             */
            public static function wrap($callback, $parameters = [])
            {            //Method inherited from \Illuminate\Container\Container
                // @var \Illuminate\Foundation\Application $instance
                return $instance->wrap($callback, $parameters);
            }
        }
        /**
         * @see \Illuminate\Contracts\Console\Kernel
         */
        class Artisan
        {
            /**
             * Get all of the commands registered with the console.
             *
             * @return array
             * @static
             */
            public static function all()
            {            //Method inherited from \Illuminate\Foundation\Console\Kernel
                // @var \App\Console\Kernel $instance
                return $instance->all();
            }

            /**
             * Bootstrap the application for artisan commands.
             *
             * @return void
             * @static
             */
            public static function bootstrap()
            {            //Method inherited from \Illuminate\Foundation\Console\Kernel
                // @var \App\Console\Kernel $instance
                $instance->bootstrap();
            }

            /**
             * Run an Artisan console command by name.
             *
             * @param  string                                                        $command
             * @param  array                                                         $parameters
             * @param  \Symfony\Component\Console\Output\OutputInterface|null        $outputBuffer
             * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
             * @return int
             * @static
             */
            public static function call($command, $parameters = [], $outputBuffer = null)
            {            //Method inherited from \Illuminate\Foundation\Console\Kernel
                // @var \App\Console\Kernel $instance
                return $instance->call($command, $parameters, $outputBuffer);
            }

            /**
             * Register a Closure based command with the application.
             *
             * @param  string                                        $signature
             * @param  \Closure                                      $callback
             * @return \Illuminate\Foundation\Console\ClosureCommand
             * @static
             */
            public static function command($signature, $callback)
            {            //Method inherited from \Illuminate\Foundation\Console\Kernel
                // @var \App\Console\Kernel $instance
                return $instance->command($signature, $callback);
            }

            /**
             * Run the console application.
             *
             * @param  \Symfony\Component\Console\Input\InputInterface        $input
             * @param  \Symfony\Component\Console\Output\OutputInterface|null $output
             * @return int
             * @static
             */
            public static function handle($input, $output = null)
            {            //Method inherited from \Illuminate\Foundation\Console\Kernel
                // @var \App\Console\Kernel $instance
                return $instance->handle($input, $output);
            }

            /**
             * Get the output for the last run command.
             *
             * @return string
             * @static
             */
            public static function output()
            {            //Method inherited from \Illuminate\Foundation\Console\Kernel
                // @var \App\Console\Kernel $instance
                return $instance->output();
            }

            /**
             * Queue the given console command.
             *
             * @param  string                                     $command
             * @param  array                                      $parameters
             * @return \Illuminate\Foundation\Bus\PendingDispatch
             * @static
             */
            public static function queue($command, $parameters = [])
            {            //Method inherited from \Illuminate\Foundation\Console\Kernel
                // @var \App\Console\Kernel $instance
                return $instance->queue($command, $parameters);
            }

            /**
             * Register the given command with the console application.
             *
             * @param  \Symfony\Component\Console\Command\Command $command
             * @return void
             * @static
             */
            public static function registerCommand($command)
            {            //Method inherited from \Illuminate\Foundation\Console\Kernel
                // @var \App\Console\Kernel $instance
                $instance->registerCommand($command);
            }

            /**
             * Set the Artisan application instance.
             *
             * @param  \Illuminate\Console\Application $artisan
             * @return void
             * @static
             */
            public static function setArtisan($artisan)
            {            //Method inherited from \Illuminate\Foundation\Console\Kernel
                // @var \App\Console\Kernel $instance
                $instance->setArtisan($artisan);
            }

            /**
             * Terminate the application.
             *
             * @param  \Symfony\Component\Console\Input\InputInterface $input
             * @param  int                                             $status
             * @return void
             * @static
             */
            public static function terminate($input, $status)
            {            //Method inherited from \Illuminate\Foundation\Console\Kernel
                // @var \App\Console\Kernel $instance
                $instance->terminate($input, $status);
            }
        }
        /**
         * @see \Illuminate\Auth\AuthManager
         * @see \Illuminate\Contracts\Auth\Factory
         * @see \Illuminate\Contracts\Auth\Guard
         * @see \Illuminate\Contracts\Auth\StatefulGuard
         */
        class Auth
        {
            /**
             * Attempt to authenticate a user using the given credentials.
             *
             * @param  array $credentials
             * @param  bool  $remember
             * @return bool
             * @static
             */
            public static function attempt($credentials = [], $remember = false)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->attempt($credentials, $remember);
            }

            /**
             * Register an authentication attempt event listener.
             *
             * @param  mixed $callback
             * @return void
             * @static
             */
            public static function attempting($callback)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                $instance->attempting($callback);
            }

            /**
             * Determine if the current user is authenticated. If not, throw an exception.
             *
             * @throws \Illuminate\Auth\AuthenticationException
             * @return \App\User
             * @static
             */
            public static function authenticate()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->authenticate();
            }

            /**
             * Attempt to authenticate using HTTP Basic Auth.
             *
             * @param  string                                          $field
             * @param  array                                           $extraConditions
             * @return \Symfony\Component\HttpFoundation\Response|null
             * @static
             */
            public static function basic($field = 'email', $extraConditions = [])
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->basic($field, $extraConditions);
            }

            /**
             * Determine if the current user is authenticated.
             *
             * @return bool
             * @static
             */
            public static function check()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->check();
            }

            /**
             * Create a session based authentication guard.
             *
             * @param  string                        $name
             * @param  array                         $config
             * @return \Illuminate\Auth\SessionGuard
             * @static
             */
            public static function createSessionDriver($name, $config)
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->createSessionDriver($name, $config);
            }

            /**
             * Create a token based authentication guard.
             *
             * @param  string                      $name
             * @param  array                       $config
             * @return \Illuminate\Auth\TokenGuard
             * @static
             */
            public static function createTokenDriver($name, $config)
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->createTokenDriver($name, $config);
            }

            /**
             * Create the user provider implementation for the driver.
             *
             * @param  string|null                                  $provider
             * @throws \InvalidArgumentException
             * @return \Illuminate\Contracts\Auth\UserProvider|null
             * @static
             */
            public static function createUserProvider($provider = null)
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->createUserProvider($provider);
            }

            /**
             * Register a custom driver creator Closure.
             *
             * @param  string                       $driver
             * @param  \Closure                     $callback
             * @return \Illuminate\Auth\AuthManager
             * @static
             */
            public static function extend($driver, $callback)
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->extend($driver, $callback);
            }

            /**
             * Get the cookie creator instance used by the guard.
             *
             * @throws \RuntimeException
             * @return \Illuminate\Contracts\Cookie\QueueingFactory
             * @static
             */
            public static function getCookieJar()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->getCookieJar();
            }

            /**
             * Get the default authentication driver name.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Get the default user provider name.
             *
             * @return string
             * @static
             */
            public static function getDefaultUserProvider()
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->getDefaultUserProvider();
            }

            /**
             * Get the event dispatcher instance.
             *
             * @return \Illuminate\Contracts\Events\Dispatcher
             * @static
             */
            public static function getDispatcher()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->getDispatcher();
            }

            /**
             * Get the last user we attempted to authenticate.
             *
             * @return \App\User
             * @static
             */
            public static function getLastAttempted()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->getLastAttempted();
            }

            /**
             * Get a unique identifier for the auth session value.
             *
             * @return string
             * @static
             */
            public static function getName()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->getName();
            }

            /**
             * Get the user provider used by the guard.
             *
             * @return \Illuminate\Contracts\Auth\UserProvider
             * @static
             */
            public static function getProvider()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->getProvider();
            }

            /**
             * Get the name of the cookie used to store the "recaller".
             *
             * @return string
             * @static
             */
            public static function getRecallerName()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->getRecallerName();
            }

            /**
             * Get the current request instance.
             *
             * @return \Symfony\Component\HttpFoundation\Request
             * @static
             */
            public static function getRequest()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->getRequest();
            }

            /**
             * Get the session store used by the guard.
             *
             * @return \Illuminate\Contracts\Session\Session
             * @static
             */
            public static function getSession()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->getSession();
            }

            /**
             * Return the currently cached user.
             *
             * @return \App\User|null
             * @static
             */
            public static function getUser()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->getUser();
            }

            /**
             * Attempt to get the guard from the local cache.
             *
             * @param  string|null                                                               $name
             * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
             * @static
             */
            public static function guard($name = null)
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->guard($name);
            }

            /**
             * Determine if the current user is a guest.
             *
             * @return bool
             * @static
             */
            public static function guest()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->guest();
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                return \Lab404\Impersonate\Guard\SessionGuard::hasMacro($name);
            }

            /**
             * Determines if any guards have already been resolved.
             *
             * @return bool
             * @static
             */
            public static function hasResolvedGuards()
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->hasResolvedGuards();
            }

            /**
             * Determine if the guard has a user instance.
             *
             * @return bool
             * @static
             */
            public static function hasUser()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->hasUser();
            }

            /**
             * Get the ID for the currently authenticated user.
             *
             * @return int|string|null
             * @static
             */
            public static function id()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->id();
            }

            /**
             * Log a user into the application.
             *
             * @param  \Illuminate\Contracts\Auth\Authenticatable $user
             * @param  bool                                       $remember
             * @return void
             * @static
             */
            public static function login($user, $remember = false)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                $instance->login($user, $remember);
            }

            /**
             * Log the given user ID into the application.
             *
             * @param  mixed           $id
             * @param  bool            $remember
             * @return \App\User|false
             * @static
             */
            public static function loginUsingId($id, $remember = false)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->loginUsingId($id, $remember);
            }

            /**
             * Log the user out of the application.
             *
             * @return void
             * @static
             */
            public static function logout()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                $instance->logout();
            }

            /**
             * Log the user out of the application on their current device only.
             *
             * This method does not cycle the "remember" token.
             *
             * @return void
             * @static
             */
            public static function logoutCurrentDevice()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                $instance->logoutCurrentDevice();
            }

            /**
             * Invalidate other sessions for the current user.
             *
             * The application must be using the AuthenticateSession middleware.
             *
             * @param  string    $password
             * @param  string    $attribute
             * @return bool|null
             * @static
             */
            public static function logoutOtherDevices($password, $attribute = 'password')
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->logoutOtherDevices($password, $attribute);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                \Lab404\Impersonate\Guard\SessionGuard::macro($name, $macro);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                \Lab404\Impersonate\Guard\SessionGuard::mixin($mixin, $replace);
            }

            /**
             * Log a user into the application without sessions or cookies.
             *
             * @param  array $credentials
             * @return bool
             * @static
             */
            public static function once($credentials = [])
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->once($credentials);
            }

            /**
             * Perform a stateless HTTP Basic login attempt.
             *
             * @param  string                                          $field
             * @param  array                                           $extraConditions
             * @return \Symfony\Component\HttpFoundation\Response|null
             * @static
             */
            public static function onceBasic($field = 'email', $extraConditions = [])
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->onceBasic($field, $extraConditions);
            }

            /**
             * Log the given user ID into the application without sessions or cookies.
             *
             * @param  mixed           $id
             * @return \App\User|false
             * @static
             */
            public static function onceUsingId($id)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->onceUsingId($id);
            }

            /**
             * Register a custom provider creator Closure.
             *
             * @param  string                       $name
             * @param  \Closure                     $callback
             * @return \Illuminate\Auth\AuthManager
             * @static
             */
            public static function provider($name, $callback)
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->provider($name, $callback);
            }

            /**
             * Log a user into the application without firing the Login event.
             *
             * @param  \Illuminate\Contracts\Auth\Authenticatable $user
             * @return void
             * @static
             */
            public static function quietLogin($user)
            {
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                $instance->quietLogin($user);
            }

            /**
             * Logout the user without updating remember_token
             * and without firing the Logout event.
             *
             * @param void
             * @return void
             * @static
             */
            public static function quietLogout()
            {
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                $instance->quietLogout();
            }

            /**
             * Set the callback to be used to resolve users.
             *
             * @param  \Closure                     $userResolver
             * @return \Illuminate\Auth\AuthManager
             * @static
             */
            public static function resolveUsersUsing($userResolver)
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->resolveUsersUsing($userResolver);
            }

            /**
             * Set the cookie creator instance used by the guard.
             *
             * @param  \Illuminate\Contracts\Cookie\QueueingFactory $cookie
             * @return void
             * @static
             */
            public static function setCookieJar($cookie)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                $instance->setCookieJar($cookie);
            }

            /**
             * Set the default authentication driver name.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function setDefaultDriver($name)
            {
                // @var \Illuminate\Auth\AuthManager $instance
                $instance->setDefaultDriver($name);
            }

            /**
             * Set the event dispatcher instance.
             *
             * @param  \Illuminate\Contracts\Events\Dispatcher $events
             * @return void
             * @static
             */
            public static function setDispatcher($events)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                $instance->setDispatcher($events);
            }

            /**
             * Set the user provider used by the guard.
             *
             * @param  \Illuminate\Contracts\Auth\UserProvider $provider
             * @return void
             * @static
             */
            public static function setProvider($provider)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                $instance->setProvider($provider);
            }

            /**
             * Set the current request instance.
             *
             * @param  \Symfony\Component\HttpFoundation\Request $request
             * @return \Lab404\Impersonate\Guard\SessionGuard
             * @static
             */
            public static function setRequest($request)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->setRequest($request);
            }

            /**
             * Set the current user.
             *
             * @param  \Illuminate\Contracts\Auth\Authenticatable $user
             * @return \Lab404\Impersonate\Guard\SessionGuard
             * @static
             */
            public static function setUser($user)
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->setUser($user);
            }

            /**
             * Set the default guard driver the factory should serve.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function shouldUse($name)
            {
                // @var \Illuminate\Auth\AuthManager $instance
                $instance->shouldUse($name);
            }

            /**
             * Get the currently authenticated user.
             *
             * @return \App\User|null
             * @static
             */
            public static function user()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->user();
            }

            /**
             * Get the user resolver callback.
             *
             * @return \Closure
             * @static
             */
            public static function userResolver()
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->userResolver();
            }

            /**
             * Validate a user's credentials.
             *
             * @param  array $credentials
             * @return bool
             * @static
             */
            public static function validate($credentials = [])
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->validate($credentials);
            }

            /**
             * Determine if the user was authenticated via "remember me" cookie.
             *
             * @return bool
             * @static
             */
            public static function viaRemember()
            {            //Method inherited from \Illuminate\Auth\SessionGuard
                // @var \Lab404\Impersonate\Guard\SessionGuard $instance
                return $instance->viaRemember();
            }

            /**
             * Register a new callback based request guard.
             *
             * @param  string                       $driver
             * @param  callable                     $callback
             * @return \Illuminate\Auth\AuthManager
             * @static
             */
            public static function viaRequest($driver, $callback)
            {
                // @var \Illuminate\Auth\AuthManager $instance
                return $instance->viaRequest($driver, $callback);
            }
        }
        /**
         * @see \Illuminate\View\Compilers\BladeCompiler
         */
        class Blade
        {
            /**
             * Register a component alias directive.
             *
             * @param  string      $path
             * @param  string|null $alias
             * @return void
             * @static
             */
            public static function aliasComponent($path, $alias = null)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->aliasComponent($path, $alias);
            }

            /**
             * Register an include alias directive.
             *
             * @param  string      $path
             * @param  string|null $alias
             * @return void
             * @static
             */
            public static function aliasInclude($path, $alias = null)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->aliasInclude($path, $alias);
            }

            /**
             * Check the result of a condition.
             *
             * @param  string $name
             * @param  array  $parameters
             * @return bool
             * @static
             */
            public static function check($name, ...$parameters)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->check($name, ...$parameters);
            }

            /**
             * Compile the view at the given path.
             *
             * @param  string|null $path
             * @return void
             * @static
             */
            public static function compile($path = null)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->compile($path);
            }

            /**
             * Compile a class component opening.
             *
             * @param  string $component
             * @param  string $alias
             * @param  string $data
             * @param  string $hash
             * @return string
             * @static
             */
            public static function compileClassComponentOpening($component, $alias, $data, $hash)
            {
                return \Illuminate\View\Compilers\BladeCompiler::compileClassComponentOpening($component, $alias, $data, $hash);
            }

            /**
             * Compile Blade echos into valid PHP.
             *
             * @param  string $value
             * @return string
             * @static
             */
            public static function compileEchos($value)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->compileEchos($value);
            }

            /**
             * Compile the end-component statements into valid PHP.
             *
             * @return string
             * @static
             */
            public static function compileEndComponentClass()
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->compileEndComponentClass();
            }

            /**
             * Compile an end-once block into valid PHP.
             *
             * @return string
             * @static
             */
            public static function compileEndOnce()
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->compileEndOnce();
            }

            /**
             * Compile the given Blade template contents.
             *
             * @param  string $value
             * @return string
             * @static
             */
            public static function compileString($value)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->compileString($value);
            }

            /**
             * Register a class-based component alias directive.
             *
             * @param  string      $class
             * @param  string|null $alias
             * @param  string      $prefix
             * @return void
             * @static
             */
            public static function component($class, $alias = null, $prefix = '')
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->component($class, $alias, $prefix);
            }

            /**
             * Register a class-based component namespace.
             *
             * @param  string $namespace
             * @param  string $prefix
             * @return void
             * @static
             */
            public static function componentNamespace($namespace, $prefix)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->componentNamespace($namespace, $prefix);
            }

            /**
             * Register an array of class-based components.
             *
             * @param  array  $components
             * @param  string $prefix
             * @return void
             * @static
             */
            public static function components($components, $prefix = '')
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->components($components, $prefix);
            }

            /**
             * Register a handler for custom directives.
             *
             * @param  string                    $name
             * @param  callable                  $handler
             * @throws \InvalidArgumentException
             * @return void
             * @static
             */
            public static function directive($name, $handler)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->directive($name, $handler);
            }

            /**
             * Register a custom Blade compiler.
             *
             * @param  callable $compiler
             * @return void
             * @static
             */
            public static function extend($compiler)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->extend($compiler);
            }

            /**
             * Get the registered class component aliases.
             *
             * @return array
             * @static
             */
            public static function getClassComponentAliases()
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->getClassComponentAliases();
            }

            /**
             * Get the registered class component namespaces.
             *
             * @return array
             * @static
             */
            public static function getClassComponentNamespaces()
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->getClassComponentNamespaces();
            }

            /**
             * Get the path to the compiled version of a view.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function getCompiledPath($path)
            {            //Method inherited from \Illuminate\View\Compilers\Compiler
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->getCompiledPath($path);
            }

            /**
             * Get the list of custom directives.
             *
             * @return array
             * @static
             */
            public static function getCustomDirectives()
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->getCustomDirectives();
            }

            /**
             * Get the extensions used by the compiler.
             *
             * @return array
             * @static
             */
            public static function getExtensions()
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->getExtensions();
            }

            /**
             * Get the path currently being compiled.
             *
             * @return string
             * @static
             */
            public static function getPath()
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->getPath();
            }

            /**
             * Register an "if" statement directive.
             *
             * @param  string   $name
             * @param  callable $callback
             * @return void
             * @static
             */
            public static function if($name, $callback)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->if($name, $callback);
            }

            /**
             * Register an include alias directive.
             *
             * @param  string      $path
             * @param  string|null $alias
             * @return void
             * @static
             */
            public static function include($path, $alias = null)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->include($path, $alias);
            }

            /**
             * Determine if the view at the given path is expired.
             *
             * @param  string $path
             * @return bool
             * @static
             */
            public static function isExpired($path)
            {            //Method inherited from \Illuminate\View\Compilers\Compiler
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->isExpired($path);
            }

            /**
             * Get a new component hash for a component name.
             *
             * @param  string $component
             * @return string
             * @static
             */
            public static function newComponentHash($component)
            {
                return \Illuminate\View\Compilers\BladeCompiler::newComponentHash($component);
            }

            /**
             * Register a new precompiler.
             *
             * @param  callable $precompiler
             * @return void
             * @static
             */
            public static function precompiler($precompiler)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->precompiler($precompiler);
            }

            /**
             * Sanitize the given component attribute value.
             *
             * @param  mixed $value
             * @return mixed
             * @static
             */
            public static function sanitizeComponentAttribute($value)
            {
                return \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($value);
            }

            /**
             * Set the echo format to be used by the compiler.
             *
             * @param  string $format
             * @return void
             * @static
             */
            public static function setEchoFormat($format)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->setEchoFormat($format);
            }

            /**
             * Set the path currently being compiled.
             *
             * @param  string $path
             * @return void
             * @static
             */
            public static function setPath($path)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->setPath($path);
            }

            /**
             * Strip the parentheses from the given expression.
             *
             * @param  string $expression
             * @return string
             * @static
             */
            public static function stripParentheses($expression)
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                return $instance->stripParentheses($expression);
            }

            /**
             * Set the "echo" format to double encode entities.
             *
             * @return void
             * @static
             */
            public static function withDoubleEncoding()
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->withDoubleEncoding();
            }

            /**
             * Indicate that component tags should not be compiled.
             *
             * @return void
             * @static
             */
            public static function withoutComponentTags()
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->withoutComponentTags();
            }

            /**
             * Set the "echo" format to not double encode entities.
             *
             * @return void
             * @static
             */
            public static function withoutDoubleEncoding()
            {
                // @var \Illuminate\View\Compilers\BladeCompiler $instance
                $instance->withoutDoubleEncoding();
            }
        }
        /**
         * @method static \Illuminate\Broadcasting\Broadcasters\Broadcaster channel(string $channel, callable|string  $callback, array $options = [])
         * @method static mixed auth(\Illuminate\Http\Request $request)
         * @see \Illuminate\Contracts\Broadcasting\Factory
         */
        class Broadcast
        {
            /**
             * Get a driver instance.
             *
             * @param  string|null $driver
             * @return mixed
             * @static
             */
            public static function connection($driver = null)
            {
                // @var \Illuminate\Broadcasting\BroadcastManager $instance
                return $instance->connection($driver);
            }

            /**
             * Get a driver instance.
             *
             * @param  string|null $name
             * @return mixed
             * @static
             */
            public static function driver($name = null)
            {
                // @var \Illuminate\Broadcasting\BroadcastManager $instance
                return $instance->driver($name);
            }

            /**
             * Begin broadcasting an event.
             *
             * @param  mixed|null                                $event
             * @return \Illuminate\Broadcasting\PendingBroadcast
             * @static
             */
            public static function event($event = null)
            {
                // @var \Illuminate\Broadcasting\BroadcastManager $instance
                return $instance->event($event);
            }

            /**
             * Register a custom driver creator Closure.
             *
             * @param  string                                    $driver
             * @param  \Closure                                  $callback
             * @return \Illuminate\Broadcasting\BroadcastManager
             * @static
             */
            public static function extend($driver, $callback)
            {
                // @var \Illuminate\Broadcasting\BroadcastManager $instance
                return $instance->extend($driver, $callback);
            }

            /**
             * Get the default driver name.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Broadcasting\BroadcastManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Disconnect the given disk and remove from local cache.
             *
             * @param  string|null $name
             * @return void
             * @static
             */
            public static function purge($name = null)
            {
                // @var \Illuminate\Broadcasting\BroadcastManager $instance
                $instance->purge($name);
            }

            /**
             * Queue the given event for broadcast.
             *
             * @param  mixed $event
             * @return void
             * @static
             */
            public static function queue($event)
            {
                // @var \Illuminate\Broadcasting\BroadcastManager $instance
                $instance->queue($event);
            }

            /**
             * Register the routes for handling broadcast authentication and sockets.
             *
             * @param  array|null $attributes
             * @return void
             * @static
             */
            public static function routes($attributes = null)
            {
                // @var \Illuminate\Broadcasting\BroadcastManager $instance
                $instance->routes($attributes);
            }

            /**
             * Set the default driver name.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function setDefaultDriver($name)
            {
                // @var \Illuminate\Broadcasting\BroadcastManager $instance
                $instance->setDefaultDriver($name);
            }

            /**
             * Get the socket ID for the given request.
             *
             * @param  \Illuminate\Http\Request|null $request
             * @return string|null
             * @static
             */
            public static function socket($request = null)
            {
                // @var \Illuminate\Broadcasting\BroadcastManager $instance
                return $instance->socket($request);
            }
        }
        /**
         * @see \Illuminate\Contracts\Bus\Dispatcher
         */
        class Bus
        {
            /**
             * Assert if a batch was dispatched based on a truth-test callback.
             *
             * @param  callable $callback
             * @return void
             * @static
             */
            public static function assertBatched($callback)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                $instance->assertBatched($callback);
            }

            /**
             * Assert if a chain of jobs was dispatched.
             *
             * @param  array $expectedChain
             * @return void
             * @static
             */
            public static function assertChained($expectedChain)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                $instance->assertChained($expectedChain);
            }

            /**
             * Assert if a job was dispatched based on a truth-test callback.
             *
             * @param  \Closure|string   $command
             * @param  callable|int|null $callback
             * @return void
             * @static
             */
            public static function assertDispatched($command, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                $instance->assertDispatched($command, $callback);
            }

            /**
             * Assert if a job was dispatched after the response was sent based on a truth-test callback.
             *
             * @param  \Closure|string   $command
             * @param  callable|int|null $callback
             * @return void
             * @static
             */
            public static function assertDispatchedAfterResponse($command, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                $instance->assertDispatchedAfterResponse($command, $callback);
            }

            /**
             * Assert if a job was pushed after the response was sent a number of times.
             *
             * @param  string $command
             * @param  int    $times
             * @return void
             * @static
             */
            public static function assertDispatchedAfterResponseTimes($command, $times = 1)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                $instance->assertDispatchedAfterResponseTimes($command, $times);
            }

            /**
             * Assert if a job was pushed a number of times.
             *
             * @param  string $command
             * @param  int    $times
             * @return void
             * @static
             */
            public static function assertDispatchedTimes($command, $times = 1)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                $instance->assertDispatchedTimes($command, $times);
            }

            /**
             * Assert if a job was dispatched with an empty chain based on a truth-test callback.
             *
             * @param  \Closure|string $command
             * @param  callable|null   $callback
             * @return void
             * @static
             */
            public static function assertDispatchedWithoutChain($command, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                $instance->assertDispatchedWithoutChain($command, $callback);
            }

            /**
             * Determine if a job was dispatched based on a truth-test callback.
             *
             * @param  \Closure|string $command
             * @param  callable|null   $callback
             * @return void
             * @static
             */
            public static function assertNotDispatched($command, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                $instance->assertNotDispatched($command, $callback);
            }

            /**
             * Determine if a job was dispatched based on a truth-test callback.
             *
             * @param  \Closure|string $command
             * @param  callable|null   $callback
             * @return void
             * @static
             */
            public static function assertNotDispatchedAfterResponse($command, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                $instance->assertNotDispatchedAfterResponse($command, $callback);
            }

            /**
             * Create a new batch of queueable jobs.
             *
             * @param  array|\Illuminate\Support\Collection|mixed $jobs
             * @return \Illuminate\Bus\PendingBatch
             * @static
             */
            public static function batch($jobs)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->batch($jobs);
            }

            /**
             * Get all of the pending batches matching a truth-test callback.
             *
             * @param  callable                       $callback
             * @return \Illuminate\Support\Collection
             * @static
             */
            public static function batched($callback)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                return $instance->batched($callback);
            }

            /**
             * Create a new chain of queueable jobs.
             *
             * @param  array|\Illuminate\Support\Collection    $jobs
             * @return \Illuminate\Foundation\Bus\PendingChain
             * @static
             */
            public static function chain($jobs)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->chain($jobs);
            }

            /**
             * Dispatch a command to its appropriate handler.
             *
             * @param  mixed $command
             * @return mixed
             * @static
             */
            public static function dispatch($command)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->dispatch($command);
            }

            /**
             * Dispatch a command to its appropriate handler after the current process.
             *
             * @param  mixed $command
             * @param  mixed $handler
             * @return void
             * @static
             */
            public static function dispatchAfterResponse($command, $handler = null)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                $instance->dispatchAfterResponse($command, $handler);
            }

            /**
             * Get all of the jobs matching a truth-test callback.
             *
             * @param  string                         $command
             * @param  callable|null                  $callback
             * @return \Illuminate\Support\Collection
             * @static
             */
            public static function dispatched($command, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                return $instance->dispatched($command, $callback);
            }

            /**
             * Get all of the jobs dispatched after the response was sent matching a truth-test callback.
             *
             * @param  string                         $command
             * @param  callable|null                  $callback
             * @return \Illuminate\Support\Collection
             * @static
             */
            public static function dispatchedAfterResponse($command, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                return $instance->dispatchedAfterResponse($command, $callback);
            }

            /**
             * Dispatch a command to its appropriate handler in the current process without using the synchronous queue.
             *
             * @param  mixed $command
             * @param  mixed $handler
             * @return mixed
             * @static
             */
            public static function dispatchNow($command, $handler = null)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->dispatchNow($command, $handler);
            }

            /**
             * Dispatch a command to its appropriate handler in the current process.
             *
             * Queuable jobs will be dispatched to the "sync" queue.
             *
             * @param  mixed $command
             * @param  mixed $handler
             * @return mixed
             * @static
             */
            public static function dispatchSync($command, $handler = null)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->dispatchSync($command, $handler);
            }

            /**
             * Dispatch a command to its appropriate handler behind a queue.
             *
             * @param  mixed $command
             * @return mixed
             * @static
             */
            public static function dispatchToQueue($command)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->dispatchToQueue($command);
            }

            /**
             * Attempt to find the batch with the given ID.
             *
             * @param  string                     $batchId
             * @return \Illuminate\Bus\Batch|null
             * @static
             */
            public static function findBatch($batchId)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->findBatch($batchId);
            }

            /**
             * Retrieve the handler for a command.
             *
             * @param  mixed      $command
             * @return bool|mixed
             * @static
             */
            public static function getCommandHandler($command)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->getCommandHandler($command);
            }

            /**
             * Determine if the given command has a handler.
             *
             * @param  mixed $command
             * @return bool
             * @static
             */
            public static function hasCommandHandler($command)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->hasCommandHandler($command);
            }

            /**
             * Determine if there are any stored commands for a given class.
             *
             * @param  string $command
             * @return bool
             * @static
             */
            public static function hasDispatched($command)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                return $instance->hasDispatched($command);
            }

            /**
             * Determine if there are any stored commands for a given class.
             *
             * @param  string $command
             * @return bool
             * @static
             */
            public static function hasDispatchedAfterResponse($command)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                return $instance->hasDispatchedAfterResponse($command);
            }

            /**
             * Map a command to a handler.
             *
             * @param  array                      $map
             * @return \Illuminate\Bus\Dispatcher
             * @static
             */
            public static function map($map)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->map($map);
            }

            /**
             * Set the pipes through which commands should be piped before dispatching.
             *
             * @param  array                      $pipes
             * @return \Illuminate\Bus\Dispatcher
             * @static
             */
            public static function pipeThrough($pipes)
            {
                // @var \Illuminate\Bus\Dispatcher $instance
                return $instance->pipeThrough($pipes);
            }

            /**
             * Record the fake pending batch dispatch.
             *
             * @param  \Illuminate\Bus\PendingBatch $pendingBatch
             * @return \Illuminate\Bus\Batch
             * @static
             */
            public static function recordPendingBatch($pendingBatch)
            {
                // @var \Illuminate\Support\Testing\Fakes\BusFake $instance
                return $instance->recordPendingBatch($pendingBatch);
            }
        }
        /**
         * @see \Illuminate\Cache\CacheManager
         * @see \Illuminate\Cache\Repository
         */
        class Cache
        {
            /**
             * Store an item in the cache if the key does not exist.
             *
             * @param  string                                    $key
             * @param  mixed                                     $value
             * @param  \DateInterval|\DateTimeInterface|int|null $ttl
             * @return bool
             * @static
             */
            public static function add($key, $value, $ttl = null)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->add($key, $value, $ttl);
            }

            /**
             * Wipes clean the entire cache's keys.
             *
             * @return bool true on success and false on failure
             * @static
             */
            public static function clear()
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->clear();
            }

            /**
             * Get the Redis connection instance.
             *
             * @return \Illuminate\Redis\Connections\Connection
             * @static
             */
            public static function connection()
            {
                // @var \Illuminate\Cache\RedisStore $instance
                return $instance->connection();
            }

            /**
             * Decrement the value of an item in the cache.
             *
             * @param  string   $key
             * @param  mixed    $value
             * @return bool|int
             * @static
             */
            public static function decrement($key, $value = 1)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->decrement($key, $value);
            }

            /**
             * Delete an item from the cache by its unique key.
             *
             * @param  string                                    $key the unique cache key of the item to delete
             * @throws \Psr\SimpleCache\InvalidArgumentException
             *                                                       MUST be thrown if the $key string is not a legal value
             * @return bool                                      True if the item was successfully removed. False if there was an error.
             * @static
             */
            public static function delete($key)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->delete($key);
            }

            /**
             * Deletes multiple cache items in a single operation.
             *
             * @param  \Psr\SimpleCache\iterable                 $keys a list of string-based keys to be deleted
             * @throws \Psr\SimpleCache\InvalidArgumentException
             *                                                        MUST be thrown if $keys is neither an array nor a Traversable,
             *                                                        or if any of the $keys are not a legal value
             * @return bool                                      True if the items were successfully removed. False if there was an error.
             * @static
             */
            public static function deleteMultiple($keys)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->deleteMultiple($keys);
            }

            /**
             * Get a cache driver instance.
             *
             * @param  string|null                            $driver
             * @return \Illuminate\Contracts\Cache\Repository
             * @static
             */
            public static function driver($driver = null)
            {
                // @var \Illuminate\Cache\CacheManager $instance
                return $instance->driver($driver);
            }

            /**
             * Register a custom driver creator Closure.
             *
             * @param  string                         $driver
             * @param  \Closure                       $callback
             * @return \Illuminate\Cache\CacheManager
             * @static
             */
            public static function extend($driver, $callback)
            {
                // @var \Illuminate\Cache\CacheManager $instance
                return $instance->extend($driver, $callback);
            }

            /**
             * Remove all items from the cache.
             *
             * @return bool
             * @static
             */
            public static function flush()
            {
                // @var \Illuminate\Cache\RedisStore $instance
                return $instance->flush();
            }

            /**
             * Store an item in the cache indefinitely.
             *
             * @param  string $key
             * @param  mixed  $value
             * @return bool
             * @static
             */
            public static function forever($key, $value)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->forever($key, $value);
            }

            /**
             * Remove an item from the cache.
             *
             * @param  string $key
             * @return bool
             * @static
             */
            public static function forget($key)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->forget($key);
            }

            /**
             * Unset the given driver instances.
             *
             * @param  array|string|null              $name
             * @return \Illuminate\Cache\CacheManager
             * @static
             */
            public static function forgetDriver($name = null)
            {
                // @var \Illuminate\Cache\CacheManager $instance
                return $instance->forgetDriver($name);
            }

            /**
             * Retrieve an item from the cache by key.
             *
             * @param  string $key
             * @param  mixed  $default
             * @return mixed
             * @static
             */
            public static function get($key, $default = null)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->get($key, $default);
            }

            /**
             * Get the default cache time.
             *
             * @return int|null
             * @static
             */
            public static function getDefaultCacheTime()
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->getDefaultCacheTime();
            }

            /**
             * Get the default cache driver name.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Cache\CacheManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Get the event dispatcher instance.
             *
             * @return \Illuminate\Contracts\Events\Dispatcher
             * @static
             */
            public static function getEventDispatcher()
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->getEventDispatcher();
            }

            /**
             * Obtains multiple cache items by their unique keys.
             *
             * @param  \Psr\SimpleCache\iterable                 $keys    a list of keys that can obtained in a single operation
             * @param  mixed                                     $default default value to return for keys that do not exist
             * @throws \Psr\SimpleCache\InvalidArgumentException
             *                                                           MUST be thrown if $keys is neither an array nor a Traversable,
             *                                                           or if any of the $keys are not a legal value
             * @return \Psr\SimpleCache\iterable                 A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
             * @static
             */
            public static function getMultiple($keys, $default = null)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->getMultiple($keys, $default);
            }

            /**
             * Get the cache key prefix.
             *
             * @return string
             * @static
             */
            public static function getPrefix()
            {
                // @var \Illuminate\Cache\RedisStore $instance
                return $instance->getPrefix();
            }

            /**
             * Get the Redis database instance.
             *
             * @return \Illuminate\Contracts\Redis\Factory
             * @static
             */
            public static function getRedis()
            {
                // @var \Illuminate\Cache\RedisStore $instance
                return $instance->getRedis();
            }

            /**
             * Get the cache store implementation.
             *
             * @return \Illuminate\Contracts\Cache\Store
             * @static
             */
            public static function getStore()
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->getStore();
            }

            /**
             * Determine if an item exists in the cache.
             *
             * @param  string $key
             * @return bool
             * @static
             */
            public static function has($key)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->has($key);
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Cache\Repository::hasMacro($name);
            }

            /**
             * Increment the value of an item in the cache.
             *
             * @param  string   $key
             * @param  mixed    $value
             * @return bool|int
             * @static
             */
            public static function increment($key, $value = 1)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->increment($key, $value);
            }

            /**
             * Get a lock instance.
             *
             * @param  string                           $name
             * @param  int                              $seconds
             * @param  string|null                      $owner
             * @return \Illuminate\Contracts\Cache\Lock
             * @static
             */
            public static function lock($name, $seconds = 0, $owner = null)
            {
                // @var \Illuminate\Cache\RedisStore $instance
                return $instance->lock($name, $seconds, $owner);
            }

            /**
             * Get the Redis connection instance that should be used to manage locks.
             *
             * @return \Illuminate\Redis\Connections\Connection
             * @static
             */
            public static function lockConnection()
            {
                // @var \Illuminate\Cache\RedisStore $instance
                return $instance->lockConnection();
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Cache\Repository::macro($name, $macro);
            }

            /**
             * Dynamically handle calls to the class.
             *
             * @param  string                  $method
             * @param  array                   $parameters
             * @throws \BadMethodCallException
             * @return mixed
             * @static
             */
            public static function macroCall($method, $parameters)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->macroCall($method, $parameters);
            }

            /**
             * Retrieve multiple items from the cache by key.
             *
             * Items not found in the cache will have a null value.
             *
             * @param  array $keys
             * @return array
             * @static
             */
            public static function many($keys)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->many($keys);
            }

            /**
             * Determine if an item doesn't exist in the cache.
             *
             * @param  string $key
             * @return bool
             * @static
             */
            public static function missing($key)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->missing($key);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Cache\Repository::mixin($mixin, $replace);
            }

            /**
             * Determine if a cached value exists.
             *
             * @param  string $key
             * @return bool
             * @static
             */
            public static function offsetExists($key)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->offsetExists($key);
            }

            /**
             * Retrieve an item from the cache by key.
             *
             * @param  string $key
             * @return mixed
             * @static
             */
            public static function offsetGet($key)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->offsetGet($key);
            }

            /**
             * Store an item in the cache for the default time.
             *
             * @param  string $key
             * @param  mixed  $value
             * @return void
             * @static
             */
            public static function offsetSet($key, $value)
            {
                // @var \Illuminate\Cache\Repository $instance
                $instance->offsetSet($key, $value);
            }

            /**
             * Remove an item from the cache.
             *
             * @param  string $key
             * @return void
             * @static
             */
            public static function offsetUnset($key)
            {
                // @var \Illuminate\Cache\Repository $instance
                $instance->offsetUnset($key);
            }

            /**
             * Retrieve an item from the cache and delete it.
             *
             * @param  string $key
             * @param  mixed  $default
             * @return mixed
             * @static
             */
            public static function pull($key, $default = null)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->pull($key, $default);
            }

            /**
             * Disconnect the given driver and remove from local cache.
             *
             * @param  string|null $name
             * @return void
             * @static
             */
            public static function purge($name = null)
            {
                // @var \Illuminate\Cache\CacheManager $instance
                $instance->purge($name);
            }

            /**
             * Store an item in the cache.
             *
             * @param  string                                    $key
             * @param  mixed                                     $value
             * @param  \DateInterval|\DateTimeInterface|int|null $ttl
             * @return bool
             * @static
             */
            public static function put($key, $value, $ttl = null)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->put($key, $value, $ttl);
            }

            /**
             * Store multiple items in the cache for a given number of seconds.
             *
             * @param  array                                     $values
             * @param  \DateInterval|\DateTimeInterface|int|null $ttl
             * @return bool
             * @static
             */
            public static function putMany($values, $ttl = null)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->putMany($values, $ttl);
            }

            /**
             * Re-set the event dispatcher on all resolved cache repositories.
             *
             * @return void
             * @static
             */
            public static function refreshEventDispatcher()
            {
                // @var \Illuminate\Cache\CacheManager $instance
                $instance->refreshEventDispatcher();
            }

            /**
             * Get an item from the cache, or execute the given Closure and store the result.
             *
             * @param  string                                    $key
             * @param  \DateInterval|\DateTimeInterface|int|null $ttl
             * @param  \Closure                                  $callback
             * @return mixed
             * @static
             */
            public static function remember($key, $ttl, $callback)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->remember($key, $ttl, $callback);
            }

            /**
             * Get an item from the cache, or execute the given Closure and store the result forever.
             *
             * @param  string   $key
             * @param  \Closure $callback
             * @return mixed
             * @static
             */
            public static function rememberForever($key, $callback)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->rememberForever($key, $callback);
            }

            /**
             * Create a new cache repository with the given implementation.
             *
             * @param  \Illuminate\Contracts\Cache\Store $store
             * @return \Illuminate\Cache\Repository
             * @static
             */
            public static function repository($store)
            {
                // @var \Illuminate\Cache\CacheManager $instance
                return $instance->repository($store);
            }

            /**
             * Restore a lock instance using the owner identifier.
             *
             * @param  string                           $name
             * @param  string                           $owner
             * @return \Illuminate\Contracts\Cache\Lock
             * @static
             */
            public static function restoreLock($name, $owner)
            {
                // @var \Illuminate\Cache\RedisStore $instance
                return $instance->restoreLock($name, $owner);
            }

            /**
             * Get an item from the cache, or execute the given Closure and store the result forever.
             *
             * @param  string   $key
             * @param  \Closure $callback
             * @return mixed
             * @static
             */
            public static function sear($key, $callback)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->sear($key, $callback);
            }

            /**
             * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
             *
             * @param  string                                    $key   the key of the item to store
             * @param  mixed                                     $value the value of the item to store, must be serializable
             * @param  \DateInterval|int|null                    $ttl   Optional. The TTL value of this item. If no value is sent and
             *                                                          the driver supports TTL then the library may set a default value
             *                                                          for it or let the driver take care of that.
             * @throws \Psr\SimpleCache\InvalidArgumentException
             *                                                         MUST be thrown if the $key string is not a legal value
             * @return bool                                      true on success and false on failure
             * @static
             */
            public static function set($key, $value, $ttl = null)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->set($key, $value, $ttl);
            }

            /**
             * Specify the name of the connection that should be used to store data.
             *
             * @param  string $connection
             * @return void
             * @static
             */
            public static function setConnection($connection)
            {
                // @var \Illuminate\Cache\RedisStore $instance
                $instance->setConnection($connection);
            }

            /**
             * Set the default cache time in seconds.
             *
             * @param  int|null                     $seconds
             * @return \Illuminate\Cache\Repository
             * @static
             */
            public static function setDefaultCacheTime($seconds)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->setDefaultCacheTime($seconds);
            }

            /**
             * Set the default cache driver name.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function setDefaultDriver($name)
            {
                // @var \Illuminate\Cache\CacheManager $instance
                $instance->setDefaultDriver($name);
            }

            /**
             * Set the event dispatcher instance.
             *
             * @param  \Illuminate\Contracts\Events\Dispatcher $events
             * @return void
             * @static
             */
            public static function setEventDispatcher($events)
            {
                // @var \Illuminate\Cache\Repository $instance
                $instance->setEventDispatcher($events);
            }

            /**
             * Specify the name of the connection that should be used to manage locks.
             *
             * @param  string                       $connection
             * @return \Illuminate\Cache\RedisStore
             * @static
             */
            public static function setLockConnection($connection)
            {
                // @var \Illuminate\Cache\RedisStore $instance
                return $instance->setLockConnection($connection);
            }

            /**
             * Persists a set of key => value pairs in the cache, with an optional TTL.
             *
             * @param  \Psr\SimpleCache\iterable                 $values a list of key => value pairs for a multiple-set operation
             * @param  \DateInterval|int|null                    $ttl    Optional. The TTL value of this item. If no value is sent and
             *                                                           the driver supports TTL then the library may set a default value
             *                                                           for it or let the driver take care of that.
             * @throws \Psr\SimpleCache\InvalidArgumentException
             *                                                          MUST be thrown if $values is neither an array nor a Traversable,
             *                                                          or if any of the $values are not a legal value
             * @return bool                                      true on success and false on failure
             * @static
             */
            public static function setMultiple($values, $ttl = null)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->setMultiple($values, $ttl);
            }

            /**
             * Set the cache key prefix.
             *
             * @param  string $prefix
             * @return void
             * @static
             */
            public static function setPrefix($prefix)
            {
                // @var \Illuminate\Cache\RedisStore $instance
                $instance->setPrefix($prefix);
            }

            /**
             * Get a cache store instance by name, wrapped in a repository.
             *
             * @param  string|null                            $name
             * @return \Illuminate\Contracts\Cache\Repository
             * @static
             */
            public static function store($name = null)
            {
                // @var \Illuminate\Cache\CacheManager $instance
                return $instance->store($name);
            }

            /**
             * Determine if the current store supports tags.
             *
             * @return bool
             * @static
             */
            public static function supportsTags()
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->supportsTags();
            }

            /**
             * Begin executing a new tags operation if the store supports it.
             *
             * @param  array|mixed                   $names
             * @throws \BadMethodCallException
             * @return \Illuminate\Cache\TaggedCache
             * @static
             */
            public static function tags($names)
            {
                // @var \Illuminate\Cache\Repository $instance
                return $instance->tags($names);
            }
        }
        /**
         * @see \Illuminate\Config\Repository
         */
        class Config
        {
            /**
             * Get all of the configuration items for the application.
             *
             * @return array
             * @static
             */
            public static function all()
            {
                // @var \Illuminate\Config\Repository $instance
                return $instance->all();
            }

            /**
             * Get the specified configuration value.
             *
             * @param  array|string $key
             * @param  mixed        $default
             * @return mixed
             * @static
             */
            public static function get($key, $default = null)
            {
                // @var \Illuminate\Config\Repository $instance
                return $instance->get($key, $default);
            }

            /**
             * Get many configuration values.
             *
             * @param  array $keys
             * @return array
             * @static
             */
            public static function getMany($keys)
            {
                // @var \Illuminate\Config\Repository $instance
                return $instance->getMany($keys);
            }

            /**
             * Determine if the given configuration value exists.
             *
             * @param  string $key
             * @return bool
             * @static
             */
            public static function has($key)
            {
                // @var \Illuminate\Config\Repository $instance
                return $instance->has($key);
            }

            /**
             * Determine if the given configuration option exists.
             *
             * @param  string $key
             * @return bool
             * @static
             */
            public static function offsetExists($key)
            {
                // @var \Illuminate\Config\Repository $instance
                return $instance->offsetExists($key);
            }

            /**
             * Get a configuration option.
             *
             * @param  string $key
             * @return mixed
             * @static
             */
            public static function offsetGet($key)
            {
                // @var \Illuminate\Config\Repository $instance
                return $instance->offsetGet($key);
            }

            /**
             * Set a configuration option.
             *
             * @param  string $key
             * @param  mixed  $value
             * @return void
             * @static
             */
            public static function offsetSet($key, $value)
            {
                // @var \Illuminate\Config\Repository $instance
                $instance->offsetSet($key, $value);
            }

            /**
             * Unset a configuration option.
             *
             * @param  string $key
             * @return void
             * @static
             */
            public static function offsetUnset($key)
            {
                // @var \Illuminate\Config\Repository $instance
                $instance->offsetUnset($key);
            }

            /**
             * Prepend a value onto an array configuration value.
             *
             * @param  string $key
             * @param  mixed  $value
             * @return void
             * @static
             */
            public static function prepend($key, $value)
            {
                // @var \Illuminate\Config\Repository $instance
                $instance->prepend($key, $value);
            }

            /**
             * Push a value onto an array configuration value.
             *
             * @param  string $key
             * @param  mixed  $value
             * @return void
             * @static
             */
            public static function push($key, $value)
            {
                // @var \Illuminate\Config\Repository $instance
                $instance->push($key, $value);
            }

            /**
             * Set a given configuration value.
             *
             * @param  array|string $key
             * @param  mixed        $value
             * @return void
             * @static
             */
            public static function set($key, $value = null)
            {
                // @var \Illuminate\Config\Repository $instance
                $instance->set($key, $value);
            }
        }
        /**
         * @see \Illuminate\Cookie\CookieJar
         */
        class Cookie
        {
            /**
             * Create a cookie that lasts "forever" (five years).
             *
             * @param  string                                   $name
             * @param  string                                   $value
             * @param  string|null                              $path
             * @param  string|null                              $domain
             * @param  bool|null                                $secure
             * @param  bool                                     $httpOnly
             * @param  bool                                     $raw
             * @param  string|null                              $sameSite
             * @return \Symfony\Component\HttpFoundation\Cookie
             * @static
             */
            public static function forever($name, $value, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
            {
                // @var \Illuminate\Cookie\CookieJar $instance
                return $instance->forever($name, $value, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
            }

            /**
             * Expire the given cookie.
             *
             * @param  string                                   $name
             * @param  string|null                              $path
             * @param  string|null                              $domain
             * @return \Symfony\Component\HttpFoundation\Cookie
             * @static
             */
            public static function forget($name, $path = null, $domain = null)
            {
                // @var \Illuminate\Cookie\CookieJar $instance
                return $instance->forget($name, $path, $domain);
            }

            /**
             * Get the cookies which have been queued for the next request.
             *
             * @return \Symfony\Component\HttpFoundation\Cookie[]
             * @static
             */
            public static function getQueuedCookies()
            {
                // @var \Illuminate\Cookie\CookieJar $instance
                return $instance->getQueuedCookies();
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Cookie\CookieJar::hasMacro($name);
            }

            /**
             * Determine if a cookie has been queued.
             *
             * @param  string      $key
             * @param  string|null $path
             * @return bool
             * @static
             */
            public static function hasQueued($key, $path = null)
            {
                // @var \Illuminate\Cookie\CookieJar $instance
                return $instance->hasQueued($key, $path);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Cookie\CookieJar::macro($name, $macro);
            }

            /**
             * Create a new cookie instance.
             *
             * @param  string                                   $name
             * @param  string                                   $value
             * @param  int                                      $minutes
             * @param  string|null                              $path
             * @param  string|null                              $domain
             * @param  bool|null                                $secure
             * @param  bool                                     $httpOnly
             * @param  bool                                     $raw
             * @param  string|null                              $sameSite
             * @return \Symfony\Component\HttpFoundation\Cookie
             * @static
             */
            public static function make($name, $value, $minutes = 0, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null)
            {
                // @var \Illuminate\Cookie\CookieJar $instance
                return $instance->make($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Cookie\CookieJar::mixin($mixin, $replace);
            }

            /**
             * Queue a cookie to send with the next response.
             *
             * @param  array $parameters
             * @return void
             * @static
             */
            public static function queue(...$parameters)
            {
                // @var \Illuminate\Cookie\CookieJar $instance
                $instance->queue(...$parameters);
            }

            /**
             * Get a queued cookie instance.
             *
             * @param  string                                        $key
             * @param  mixed                                         $default
             * @param  string|null                                   $path
             * @return \Symfony\Component\HttpFoundation\Cookie|null
             * @static
             */
            public static function queued($key, $default = null, $path = null)
            {
                // @var \Illuminate\Cookie\CookieJar $instance
                return $instance->queued($key, $default, $path);
            }

            /**
             * Set the default path and domain for the jar.
             *
             * @param  string                       $path
             * @param  string                       $domain
             * @param  bool                         $secure
             * @param  string|null                  $sameSite
             * @return \Illuminate\Cookie\CookieJar
             * @static
             */
            public static function setDefaultPathAndDomain($path, $domain, $secure = false, $sameSite = null)
            {
                // @var \Illuminate\Cookie\CookieJar $instance
                return $instance->setDefaultPathAndDomain($path, $domain, $secure, $sameSite);
            }

            /**
             * Remove a cookie from the queue.
             *
             * @param  string      $name
             * @param  string|null $path
             * @return void
             * @static
             */
            public static function unqueue($name, $path = null)
            {
                // @var \Illuminate\Cookie\CookieJar $instance
                $instance->unqueue($name, $path);
            }
        }
        /**
         * @see \Illuminate\Encryption\Encrypter
         */
        class Crypt
        {
            /**
             * Decrypt the given value.
             *
             * @param  string                                            $payload
             * @param  bool                                              $unserialize
             * @throws \Illuminate\Contracts\Encryption\DecryptException
             * @return mixed
             * @static
             */
            public static function decrypt($payload, $unserialize = true)
            {
                // @var \Illuminate\Encryption\Encrypter $instance
                return $instance->decrypt($payload, $unserialize);
            }

            /**
             * Decrypt the given string without unserialization.
             *
             * @param  string                                            $payload
             * @throws \Illuminate\Contracts\Encryption\DecryptException
             * @return string
             * @static
             */
            public static function decryptString($payload)
            {
                // @var \Illuminate\Encryption\Encrypter $instance
                return $instance->decryptString($payload);
            }

            /**
             * Encrypt the given value.
             *
             * @param  mixed                                             $value
             * @param  bool                                              $serialize
             * @throws \Illuminate\Contracts\Encryption\EncryptException
             * @return string
             * @static
             */
            public static function encrypt($value, $serialize = true)
            {
                // @var \Illuminate\Encryption\Encrypter $instance
                return $instance->encrypt($value, $serialize);
            }

            /**
             * Encrypt a string without serialization.
             *
             * @param  string                                            $value
             * @throws \Illuminate\Contracts\Encryption\EncryptException
             * @return string
             * @static
             */
            public static function encryptString($value)
            {
                // @var \Illuminate\Encryption\Encrypter $instance
                return $instance->encryptString($value);
            }

            /**
             * Create a new encryption key for the given cipher.
             *
             * @param  string $cipher
             * @return string
             * @static
             */
            public static function generateKey($cipher)
            {
                return \Illuminate\Encryption\Encrypter::generateKey($cipher);
            }

            /**
             * Get the encryption key.
             *
             * @return string
             * @static
             */
            public static function getKey()
            {
                // @var \Illuminate\Encryption\Encrypter $instance
                return $instance->getKey();
            }

            /**
             * Determine if the given key and cipher combination is valid.
             *
             * @param  string $key
             * @param  string $cipher
             * @return bool
             * @static
             */
            public static function supported($key, $cipher)
            {
                return \Illuminate\Encryption\Encrypter::supported($key, $cipher);
            }
        }
        /**
         * @see \Illuminate\Database\DatabaseManager
         * @see \Illuminate\Database\Connection
         */
        class DB
        {
            /**
             * Run an SQL statement and get the number of rows affected.
             *
             * @param  string $query
             * @param  array  $bindings
             * @return int
             * @static
             */
            public static function affectingStatement($query, $bindings = [])
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->affectingStatement($query, $bindings);
            }

            /**
             * Execute the callback after a transaction commits.
             *
             * @param  callable $callback
             * @return void
             * @static
             */
            public static function afterCommit($callback)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->afterCommit($callback);
            }

            /**
             * Get all of the drivers that are actually available.
             *
             * @return array
             * @static
             */
            public static function availableDrivers()
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                return $instance->availableDrivers();
            }

            /**
             * Start a new database transaction.
             *
             * @throws \Throwable
             * @return void
             * @static
             */
            public static function beginTransaction()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->beginTransaction();
            }

            /**
             * Bind values to their parameters in the given statement.
             *
             * @param  \PDOStatement $statement
             * @param  array         $bindings
             * @return void
             * @static
             */
            public static function bindValues($statement, $bindings)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->bindValues($statement, $bindings);
            }

            /**
             * Commit the active database transaction.
             *
             * @throws \Throwable
             * @return void
             * @static
             */
            public static function commit()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->commit();
            }

            /**
             * Get a database connection instance.
             *
             * @param  string|null                     $name
             * @return \Illuminate\Database\Connection
             * @static
             */
            public static function connection($name = null)
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                return $instance->connection($name);
            }

            /**
             * Run a select statement against the database and returns a generator.
             *
             * @param  string     $query
             * @param  array      $bindings
             * @param  bool       $useReadPdo
             * @return \Generator
             * @static
             */
            public static function cursor($query, $bindings = [], $useReadPdo = true)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->cursor($query, $bindings, $useReadPdo);
            }

            /**
             * Run a delete statement against the database.
             *
             * @param  string $query
             * @param  array  $bindings
             * @return int
             * @static
             */
            public static function delete($query, $bindings = [])
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->delete($query, $bindings);
            }

            /**
             * Disable the query log on the connection.
             *
             * @return void
             * @static
             */
            public static function disableQueryLog()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->disableQueryLog();
            }

            /**
             * Disconnect from the given database.
             *
             * @param  string|null $name
             * @return void
             * @static
             */
            public static function disconnect($name = null)
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                $instance->disconnect($name);
            }

            /**
             * Enable the query log on the connection.
             *
             * @return void
             * @static
             */
            public static function enableQueryLog()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->enableQueryLog();
            }

            /**
             * Register an extension connection resolver.
             *
             * @param  string   $name
             * @param  callable $resolver
             * @return void
             * @static
             */
            public static function extend($name, $resolver)
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                $instance->extend($name, $resolver);
            }

            /**
             * Clear the query log.
             *
             * @return void
             * @static
             */
            public static function flushQueryLog()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->flushQueryLog();
            }

            /**
             * Get an option from the configuration options.
             *
             * @param  string|null $option
             * @return mixed
             * @static
             */
            public static function getConfig($option = null)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getConfig($option);
            }

            /**
             * Return all of the created connections.
             *
             * @return array
             * @static
             */
            public static function getConnections()
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                return $instance->getConnections();
            }

            /**
             * Get the name of the connected database.
             *
             * @return string
             * @static
             */
            public static function getDatabaseName()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getDatabaseName();
            }

            /**
             * Get the default connection name.
             *
             * @return string
             * @static
             */
            public static function getDefaultConnection()
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                return $instance->getDefaultConnection();
            }

            /**
             * Get a Doctrine Schema Column instance.
             *
             * @param  string                       $table
             * @param  string                       $column
             * @return \Doctrine\DBAL\Schema\Column
             * @static
             */
            public static function getDoctrineColumn($table, $column)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getDoctrineColumn($table, $column);
            }

            /**
             * Get the Doctrine DBAL database connection instance.
             *
             * @return \Doctrine\DBAL\Connection
             * @static
             */
            public static function getDoctrineConnection()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getDoctrineConnection();
            }

            /**
             * Get the Doctrine DBAL schema manager for the connection.
             *
             * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
             * @static
             */
            public static function getDoctrineSchemaManager()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getDoctrineSchemaManager();
            }

            /**
             * Get the PDO driver name.
             *
             * @return string
             * @static
             */
            public static function getDriverName()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getDriverName();
            }

            /**
             * Get the event dispatcher used by the connection.
             *
             * @return \Illuminate\Contracts\Events\Dispatcher
             * @static
             */
            public static function getEventDispatcher()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getEventDispatcher();
            }

            /**
             * Get the database connection name.
             *
             * @return string|null
             * @static
             */
            public static function getName()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getName();
            }

            /**
             * Get the current PDO connection.
             *
             * @return \PDO
             * @static
             */
            public static function getPdo()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getPdo();
            }

            /**
             * Get the query post processor used by the connection.
             *
             * @return \Illuminate\Database\Query\Processors\Processor
             * @static
             */
            public static function getPostProcessor()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getPostProcessor();
            }

            /**
             * Get the query grammar used by the connection.
             *
             * @return \Illuminate\Database\Query\Grammars\Grammar
             * @static
             */
            public static function getQueryGrammar()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getQueryGrammar();
            }

            /**
             * Get the connection query log.
             *
             * @return array
             * @static
             */
            public static function getQueryLog()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getQueryLog();
            }

            /**
             * Get the current PDO connection parameter without executing any reconnect logic.
             *
             * @return \Closure|\PDO|null
             * @static
             */
            public static function getRawPdo()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getRawPdo();
            }

            /**
             * Get the current read PDO connection parameter without executing any reconnect logic.
             *
             * @return \Closure|\PDO|null
             * @static
             */
            public static function getRawReadPdo()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getRawReadPdo();
            }

            /**
             * Get the current PDO connection used for reading.
             *
             * @return \PDO
             * @static
             */
            public static function getReadPdo()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getReadPdo();
            }

            /**
             * Get the connection resolver for the given driver.
             *
             * @param  string $driver
             * @return mixed
             * @static
             */
            public static function getResolver($driver)
            {            //Method inherited from \Illuminate\Database\Connection
                return \Illuminate\Database\MySqlConnection::getResolver($driver);
            }

            /**
             * Get a schema builder instance for the connection.
             *
             * @return \Illuminate\Database\Schema\MySqlBuilder
             * @static
             */
            public static function getSchemaBuilder()
            {
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getSchemaBuilder();
            }

            /**
             * Get the schema grammar used by the connection.
             *
             * @return \Illuminate\Database\Schema\Grammars\Grammar
             * @static
             */
            public static function getSchemaGrammar()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getSchemaGrammar();
            }

            /**
             * Get the schema state for the connection.
             *
             * @param  \Illuminate\Filesystem\Filesystem|null       $files
             * @param  callable|null                                $processFactory
             * @return \Illuminate\Database\Schema\MySqlSchemaState
             * @static
             */
            public static function getSchemaState($files = null, $processFactory = null)
            {
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getSchemaState($files, $processFactory);
            }

            /**
             * Get the table prefix for the connection.
             *
             * @return string
             * @static
             */
            public static function getTablePrefix()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->getTablePrefix();
            }

            /**
             * Run an insert statement against the database.
             *
             * @param  string $query
             * @param  array  $bindings
             * @return bool
             * @static
             */
            public static function insert($query, $bindings = [])
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->insert($query, $bindings);
            }

            /**
             * Is Doctrine available?
             *
             * @return bool
             * @static
             */
            public static function isDoctrineAvailable()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->isDoctrineAvailable();
            }

            /**
             * Determine if the connected database is a MariaDB database.
             *
             * @return bool
             * @static
             */
            public static function isMaria()
            {
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->isMaria();
            }

            /**
             * Register a database query listener with the connection.
             *
             * @param  \Closure $callback
             * @return void
             * @static
             */
            public static function listen($callback)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->listen($callback);
            }

            /**
             * Determine whether we're logging queries.
             *
             * @return bool
             * @static
             */
            public static function logging()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->logging();
            }

            /**
             * Log a query in the connection's query log.
             *
             * @param  string     $query
             * @param  array      $bindings
             * @param  float|null $time
             * @return void
             * @static
             */
            public static function logQuery($query, $bindings, $time = null)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->logQuery($query, $bindings, $time);
            }

            /**
             * Prepare the query bindings for execution.
             *
             * @param  array $bindings
             * @return array
             * @static
             */
            public static function prepareBindings($bindings)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->prepareBindings($bindings);
            }

            /**
             * Execute the given callback in "dry run" mode.
             *
             * @param  \Closure $callback
             * @return array
             * @static
             */
            public static function pretend($callback)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->pretend($callback);
            }

            /**
             * Determine if the connection is in a "dry run".
             *
             * @return bool
             * @static
             */
            public static function pretending()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->pretending();
            }

            /**
             * Disconnect from the given database and remove from local cache.
             *
             * @param  string|null $name
             * @return void
             * @static
             */
            public static function purge($name = null)
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                $instance->purge($name);
            }

            /**
             * Get a new query builder instance.
             *
             * @return \Illuminate\Database\Query\Builder
             * @static
             */
            public static function query()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->query();
            }

            /**
             * Get a new raw query expression.
             *
             * @param  mixed                                 $value
             * @return \Illuminate\Database\Query\Expression
             * @static
             */
            public static function raw($value)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->raw($value);
            }

            /**
             * Reconnect to the given database.
             *
             * @param  string|null                     $name
             * @return \Illuminate\Database\Connection
             * @static
             */
            public static function reconnect($name = null)
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                return $instance->reconnect($name);
            }

            /**
             * Indicate if any records have been modified.
             *
             * @param  bool $value
             * @return void
             * @static
             */
            public static function recordsHaveBeenModified($value = true)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->recordsHaveBeenModified($value);
            }

            /**
             * Register a connection resolver.
             *
             * @param  string   $driver
             * @param  \Closure $callback
             * @return void
             * @static
             */
            public static function resolverFor($driver, $callback)
            {            //Method inherited from \Illuminate\Database\Connection
                \Illuminate\Database\MySqlConnection::resolverFor($driver, $callback);
            }

            /**
             * Rollback the active database transaction.
             *
             * @param  int|null   $toLevel
             * @throws \Throwable
             * @return void
             * @static
             */
            public static function rollBack($toLevel = null)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->rollBack($toLevel);
            }

            /**
             * Run a select statement against the database.
             *
             * @param  string $query
             * @param  array  $bindings
             * @param  bool   $useReadPdo
             * @return array
             * @static
             */
            public static function select($query, $bindings = [], $useReadPdo = true)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->select($query, $bindings, $useReadPdo);
            }

            /**
             * Run a select statement against the database.
             *
             * @param  string $query
             * @param  array  $bindings
             * @return array
             * @static
             */
            public static function selectFromWriteConnection($query, $bindings = [])
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->selectFromWriteConnection($query, $bindings);
            }

            /**
             * Run a select statement and return a single result.
             *
             * @param  string $query
             * @param  array  $bindings
             * @param  bool   $useReadPdo
             * @return mixed
             * @static
             */
            public static function selectOne($query, $bindings = [], $useReadPdo = true)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->selectOne($query, $bindings, $useReadPdo);
            }

            /**
             * Set the name of the connected database.
             *
             * @param  string                               $database
             * @return \Illuminate\Database\MySqlConnection
             * @static
             */
            public static function setDatabaseName($database)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->setDatabaseName($database);
            }

            /**
             * Set the default connection name.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function setDefaultConnection($name)
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                $instance->setDefaultConnection($name);
            }

            /**
             * Set the event dispatcher instance on the connection.
             *
             * @param  \Illuminate\Contracts\Events\Dispatcher $events
             * @return \Illuminate\Database\MySqlConnection
             * @static
             */
            public static function setEventDispatcher($events)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->setEventDispatcher($events);
            }

            /**
             * Set the PDO connection.
             *
             * @param  \Closure|\PDO|null                   $pdo
             * @return \Illuminate\Database\MySqlConnection
             * @static
             */
            public static function setPdo($pdo)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->setPdo($pdo);
            }

            /**
             * Set the query post processor used by the connection.
             *
             * @param  \Illuminate\Database\Query\Processors\Processor $processor
             * @return \Illuminate\Database\MySqlConnection
             * @static
             */
            public static function setPostProcessor($processor)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->setPostProcessor($processor);
            }

            /**
             * Set the query grammar used by the connection.
             *
             * @param  \Illuminate\Database\Query\Grammars\Grammar $grammar
             * @return \Illuminate\Database\MySqlConnection
             * @static
             */
            public static function setQueryGrammar($grammar)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->setQueryGrammar($grammar);
            }

            /**
             * Set the PDO connection used for reading.
             *
             * @param  \Closure|\PDO|null                   $pdo
             * @return \Illuminate\Database\MySqlConnection
             * @static
             */
            public static function setReadPdo($pdo)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->setReadPdo($pdo);
            }

            /**
             * Set the database reconnector callback.
             *
             * @param  callable $reconnector
             * @return void
             * @static
             */
            public static function setReconnector($reconnector)
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                $instance->setReconnector($reconnector);
            }

            /**
             * Set the schema grammar used by the connection.
             *
             * @param  \Illuminate\Database\Schema\Grammars\Grammar $grammar
             * @return \Illuminate\Database\MySqlConnection
             * @static
             */
            public static function setSchemaGrammar($grammar)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->setSchemaGrammar($grammar);
            }

            /**
             * Set the table prefix in use by the connection.
             *
             * @param  string                               $prefix
             * @return \Illuminate\Database\MySqlConnection
             * @static
             */
            public static function setTablePrefix($prefix)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->setTablePrefix($prefix);
            }

            /**
             * Set the transaction manager instance on the connection.
             *
             * @param  \Illuminate\Database\DatabaseTransactionsManager $manager
             * @return \Illuminate\Database\MySqlConnection
             * @static
             */
            public static function setTransactionManager($manager)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->setTransactionManager($manager);
            }

            /**
             * Execute an SQL statement and return the boolean result.
             *
             * @param  string $query
             * @param  array  $bindings
             * @return bool
             * @static
             */
            public static function statement($query, $bindings = [])
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->statement($query, $bindings);
            }

            /**
             * Get all of the support drivers.
             *
             * @return array
             * @static
             */
            public static function supportedDrivers()
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                return $instance->supportedDrivers();
            }

            /**
             * Begin a fluent query against a database table.
             *
             * @param  \Closure|\Illuminate\Database\Query\Builder|string $table
             * @param  string|null                                        $as
             * @return \Illuminate\Database\Query\Builder
             * @static
             */
            public static function table($table, $as = null)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->table($table, $as);
            }

            /**
             * Execute a Closure within a transaction.
             *
             * @param  \Closure   $callback
             * @param  int        $attempts
             * @throws \Throwable
             * @return mixed
             * @static
             */
            public static function transaction($callback, $attempts = 1)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->transaction($callback, $attempts);
            }

            /**
             * Get the number of active transactions.
             *
             * @return int
             * @static
             */
            public static function transactionLevel()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->transactionLevel();
            }

            /**
             * Run a raw, unprepared query against the PDO connection.
             *
             * @param  string $query
             * @return bool
             * @static
             */
            public static function unprepared($query)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->unprepared($query);
            }

            /**
             * Unset the event dispatcher for this connection.
             *
             * @return void
             * @static
             */
            public static function unsetEventDispatcher()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->unsetEventDispatcher();
            }

            /**
             * Unset the transaction manager for this connection.
             *
             * @return void
             * @static
             */
            public static function unsetTransactionManager()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->unsetTransactionManager();
            }

            /**
             * Run an update statement against the database.
             *
             * @param  string $query
             * @param  array  $bindings
             * @return int
             * @static
             */
            public static function update($query, $bindings = [])
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->update($query, $bindings);
            }

            /**
             * Set the query post processor to the default implementation.
             *
             * @return void
             * @static
             */
            public static function useDefaultPostProcessor()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->useDefaultPostProcessor();
            }

            /**
             * Set the query grammar to the default implementation.
             *
             * @return void
             * @static
             */
            public static function useDefaultQueryGrammar()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->useDefaultQueryGrammar();
            }

            /**
             * Set the schema grammar to the default implementation.
             *
             * @return void
             * @static
             */
            public static function useDefaultSchemaGrammar()
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                $instance->useDefaultSchemaGrammar();
            }

            /**
             * Set the default database connection for the callback execution.
             *
             * @param  string   $name
             * @param  callable $callback
             * @return mixed
             * @static
             */
            public static function usingConnection($name, $callback)
            {
                // @var \Illuminate\Database\DatabaseManager $instance
                return $instance->usingConnection($name, $callback);
            }

            /**
             * Set the table prefix and return the grammar.
             *
             * @param  \Illuminate\Database\Grammar $grammar
             * @return \Illuminate\Database\Grammar
             * @static
             */
            public static function withTablePrefix($grammar)
            {            //Method inherited from \Illuminate\Database\Connection
                // @var \Illuminate\Database\MySqlConnection $instance
                return $instance->withTablePrefix($grammar);
            }
        }
        /**
         * @see \Illuminate\Events\Dispatcher
         */
        class Event
        {
            /**
             * Assert if an event was dispatched based on a truth-test callback.
             *
             * @param  \Closure|string   $event
             * @param  callable|int|null $callback
             * @return void
             * @static
             */
            public static function assertDispatched($event, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\EventFake $instance
                $instance->assertDispatched($event, $callback);
            }

            /**
             * Assert if an event was dispatched a number of times.
             *
             * @param  string $event
             * @param  int    $times
             * @return void
             * @static
             */
            public static function assertDispatchedTimes($event, $times = 1)
            {
                // @var \Illuminate\Support\Testing\Fakes\EventFake $instance
                $instance->assertDispatchedTimes($event, $times);
            }

            /**
             * Determine if an event was dispatched based on a truth-test callback.
             *
             * @param  \Closure|string $event
             * @param  callable|null   $callback
             * @return void
             * @static
             */
            public static function assertNotDispatched($event, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\EventFake $instance
                $instance->assertNotDispatched($event, $callback);
            }

            /**
             * Assert that no events were dispatched.
             *
             * @return void
             * @static
             */
            public static function assertNothingDispatched()
            {
                // @var \Illuminate\Support\Testing\Fakes\EventFake $instance
                $instance->assertNothingDispatched();
            }

            /**
             * Create a class based listener using the IoC container.
             *
             * @param  string   $listener
             * @param  bool     $wildcard
             * @return \Closure
             * @static
             */
            public static function createClassListener($listener, $wildcard = false)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                return $instance->createClassListener($listener, $wildcard);
            }

            /**
             * Fire an event and call the listeners.
             *
             * @param  object|string $event
             * @param  mixed         $payload
             * @param  bool          $halt
             * @return array|null
             * @static
             */
            public static function dispatch($event, $payload = [], $halt = false)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                return $instance->dispatch($event, $payload, $halt);
            }

            /**
             * Get all of the events matching a truth-test callback.
             *
             * @param  string                         $event
             * @param  callable|null                  $callback
             * @return \Illuminate\Support\Collection
             * @static
             */
            public static function dispatched($event, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\EventFake $instance
                return $instance->dispatched($event, $callback);
            }

            /**
             * Flush a set of pushed events.
             *
             * @param  string $event
             * @return void
             * @static
             */
            public static function flush($event)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                $instance->flush($event);
            }

            /**
             * Remove a set of listeners from the dispatcher.
             *
             * @param  string $event
             * @return void
             * @static
             */
            public static function forget($event)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                $instance->forget($event);
            }

            /**
             * Forget all of the pushed listeners.
             *
             * @return void
             * @static
             */
            public static function forgetPushed()
            {
                // @var \Illuminate\Events\Dispatcher $instance
                $instance->forgetPushed();
            }

            /**
             * Get all of the listeners for a given event name.
             *
             * @param  string $eventName
             * @return array
             * @static
             */
            public static function getListeners($eventName)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                return $instance->getListeners($eventName);
            }

            /**
             * Determine if the given event has been dispatched.
             *
             * @param  string $event
             * @return bool
             * @static
             */
            public static function hasDispatched($event)
            {
                // @var \Illuminate\Support\Testing\Fakes\EventFake $instance
                return $instance->hasDispatched($event);
            }

            /**
             * Determine if a given event has listeners.
             *
             * @param  string $eventName
             * @return bool
             * @static
             */
            public static function hasListeners($eventName)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                return $instance->hasListeners($eventName);
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Events\Dispatcher::hasMacro($name);
            }

            /**
             * Determine if the given event has any wildcard listeners.
             *
             * @param  string $eventName
             * @return bool
             * @static
             */
            public static function hasWildcardListeners($eventName)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                return $instance->hasWildcardListeners($eventName);
            }

            /**
             * Register an event listener with the dispatcher.
             *
             * @param  array|\Closure|string      $events
             * @param  array|\Closure|string|null $listener
             * @return void
             * @static
             */
            public static function listen($events, $listener = null)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                $instance->listen($events, $listener);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Events\Dispatcher::macro($name, $macro);
            }

            /**
             * Register an event listener with the dispatcher.
             *
             * @param  \Closure|string $listener
             * @param  bool            $wildcard
             * @return \Closure
             * @static
             */
            public static function makeListener($listener, $wildcard = false)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                return $instance->makeListener($listener, $wildcard);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Events\Dispatcher::mixin($mixin, $replace);
            }

            /**
             * Register an event and payload to be fired later.
             *
             * @param  string $event
             * @param  array  $payload
             * @return void
             * @static
             */
            public static function push($event, $payload = [])
            {
                // @var \Illuminate\Events\Dispatcher $instance
                $instance->push($event, $payload);
            }

            /**
             * Set the queue resolver implementation.
             *
             * @param  callable                      $resolver
             * @return \Illuminate\Events\Dispatcher
             * @static
             */
            public static function setQueueResolver($resolver)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                return $instance->setQueueResolver($resolver);
            }

            /**
             * Register an event subscriber with the dispatcher.
             *
             * @param  object|string $subscriber
             * @return void
             * @static
             */
            public static function subscribe($subscriber)
            {
                // @var \Illuminate\Events\Dispatcher $instance
                $instance->subscribe($subscriber);
            }

            /**
             * Fire an event until the first non-null response is returned.
             *
             * @param  object|string $event
             * @param  mixed         $payload
             * @return array|null
             * @static
             */
            public static function until($event, $payload = [])
            {
                // @var \Illuminate\Events\Dispatcher $instance
                return $instance->until($event, $payload);
            }
        }
        /**
         * @see \Illuminate\Filesystem\Filesystem
         */
        class File
        {
            /**
             * Get all of the files from the given directory (recursive).
             *
             * @param  string                                  $directory
             * @param  bool                                    $hidden
             * @return \Symfony\Component\Finder\SplFileInfo[]
             * @static
             */
            public static function allFiles($directory, $hidden = false)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->allFiles($directory, $hidden);
            }

            /**
             * Append to a file.
             *
             * @param  string $path
             * @param  string $data
             * @return int
             * @static
             */
            public static function append($path, $data)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->append($path, $data);
            }

            /**
             * Extract the trailing name component from a file path.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function basename($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->basename($path);
            }

            /**
             * Get or set UNIX mode of a file or directory.
             *
             * @param  string   $path
             * @param  int|null $mode
             * @return mixed
             * @static
             */
            public static function chmod($path, $mode = null)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->chmod($path, $mode);
            }

            /**
             * Empty the specified directory of all files and folders.
             *
             * @param  string $directory
             * @return bool
             * @static
             */
            public static function cleanDirectory($directory)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->cleanDirectory($directory);
            }

            /**
             * Copy a file to a new location.
             *
             * @param  string $path
             * @param  string $target
             * @return bool
             * @static
             */
            public static function copy($path, $target)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->copy($path, $target);
            }

            /**
             * Copy a directory from one location to another.
             *
             * @param  string   $directory
             * @param  string   $destination
             * @param  int|null $options
             * @return bool
             * @static
             */
            public static function copyDirectory($directory, $destination, $options = null)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->copyDirectory($directory, $destination, $options);
            }

            /**
             * Delete the file at a given path.
             *
             * @param  array|string $paths
             * @return bool
             * @static
             */
            public static function delete($paths)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->delete($paths);
            }

            /**
             * Remove all of the directories within a given directory.
             *
             * @param  string $directory
             * @return bool
             * @static
             */
            public static function deleteDirectories($directory)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->deleteDirectories($directory);
            }

            /**
             * Recursively delete a directory.
             *
             * The directory itself may be optionally preserved.
             *
             * @param  string $directory
             * @param  bool   $preserve
             * @return bool
             * @static
             */
            public static function deleteDirectory($directory, $preserve = false)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->deleteDirectory($directory, $preserve);
            }

            /**
             * Get all of the directories within a given directory.
             *
             * @param  string $directory
             * @return array
             * @static
             */
            public static function directories($directory)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->directories($directory);
            }

            /**
             * Extract the parent directory from a file path.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function dirname($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->dirname($path);
            }

            /**
             * Ensure a directory exists.
             *
             * @param  string $path
             * @param  int    $mode
             * @param  bool   $recursive
             * @return void
             * @static
             */
            public static function ensureDirectoryExists($path, $mode = 493, $recursive = true)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                $instance->ensureDirectoryExists($path, $mode, $recursive);
            }

            /**
             * Determine if a file or directory exists.
             *
             * @param  string $path
             * @return bool
             * @static
             */
            public static function exists($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->exists($path);
            }

            /**
             * Extract the file extension from a file path.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function extension($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->extension($path);
            }

            /**
             * Get an array of all files in a directory.
             *
             * @param  string                                  $directory
             * @param  bool                                    $hidden
             * @return \Symfony\Component\Finder\SplFileInfo[]
             * @static
             */
            public static function files($directory, $hidden = false)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->files($directory, $hidden);
            }

            /**
             * Get the contents of a file.
             *
             * @param  string                                                 $path
             * @param  bool                                                   $lock
             * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
             * @return string
             * @static
             */
            public static function get($path, $lock = false)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->get($path, $lock);
            }

            /**
             * Get the returned value of a file.
             *
             * @param  string                                                 $path
             * @param  array                                                  $data
             * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
             * @return mixed
             * @static
             */
            public static function getRequire($path, $data = [])
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->getRequire($path, $data);
            }

            /**
             * Find path names matching a given pattern.
             *
             * @param  string $pattern
             * @param  int    $flags
             * @return array
             * @static
             */
            public static function glob($pattern, $flags = 0)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->glob($pattern, $flags);
            }

            /**
             * Guess the file extension from the mime-type of a given file.
             *
             * @param  string      $path
             * @return string|null
             * @static
             */
            public static function guessExtension($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->guessExtension($path);
            }

            /**
             * Get the MD5 hash of the file at the given path.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function hash($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->hash($path);
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Filesystem\Filesystem::hasMacro($name);
            }

            /**
             * Determine if the given path is a directory.
             *
             * @param  string $directory
             * @return bool
             * @static
             */
            public static function isDirectory($directory)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->isDirectory($directory);
            }

            /**
             * Determine if the given path is a file.
             *
             * @param  string $file
             * @return bool
             * @static
             */
            public static function isFile($file)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->isFile($file);
            }

            /**
             * Determine if the given path is readable.
             *
             * @param  string $path
             * @return bool
             * @static
             */
            public static function isReadable($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->isReadable($path);
            }

            /**
             * Determine if the given path is writable.
             *
             * @param  string $path
             * @return bool
             * @static
             */
            public static function isWritable($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->isWritable($path);
            }

            /**
             * Get the file's last modification time.
             *
             * @param  string $path
             * @return int
             * @static
             */
            public static function lastModified($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->lastModified($path);
            }

            /**
             * Get the contents of a file one line at a time.
             *
             * @param  string                                                 $path
             * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
             * @return \Illuminate\Support\LazyCollection
             * @static
             */
            public static function lines($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->lines($path);
            }

            /**
             * Create a symlink to the target file or directory. On Windows, a hard link is created if the target is a file.
             *
             * @param  string $target
             * @param  string $link
             * @return void
             * @static
             */
            public static function link($target, $link)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                $instance->link($target, $link);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Filesystem\Filesystem::macro($name, $macro);
            }

            /**
             * Create a directory.
             *
             * @param  string $path
             * @param  int    $mode
             * @param  bool   $recursive
             * @param  bool   $force
             * @return bool
             * @static
             */
            public static function makeDirectory($path, $mode = 493, $recursive = false, $force = false)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->makeDirectory($path, $mode, $recursive, $force);
            }

            /**
             * Get the mime-type of a given file.
             *
             * @param  string       $path
             * @return false|string
             * @static
             */
            public static function mimeType($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->mimeType($path);
            }

            /**
             * Determine if a file or directory is missing.
             *
             * @param  string $path
             * @return bool
             * @static
             */
            public static function missing($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->missing($path);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Filesystem\Filesystem::mixin($mixin, $replace);
            }

            /**
             * Move a file to a new location.
             *
             * @param  string $path
             * @param  string $target
             * @return bool
             * @static
             */
            public static function move($path, $target)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->move($path, $target);
            }

            /**
             * Move a directory.
             *
             * @param  string $from
             * @param  string $to
             * @param  bool   $overwrite
             * @return bool
             * @static
             */
            public static function moveDirectory($from, $to, $overwrite = false)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->moveDirectory($from, $to, $overwrite);
            }

            /**
             * Extract the file name from a file path.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function name($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->name($path);
            }

            /**
             * Prepend to a file.
             *
             * @param  string $path
             * @param  string $data
             * @return int
             * @static
             */
            public static function prepend($path, $data)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->prepend($path, $data);
            }

            /**
             * Write the contents of a file.
             *
             * @param  string   $path
             * @param  string   $contents
             * @param  bool     $lock
             * @return bool|int
             * @static
             */
            public static function put($path, $contents, $lock = false)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->put($path, $contents, $lock);
            }

            /**
             * Create a relative symlink to the target file or directory.
             *
             * @param  string $target
             * @param  string $link
             * @return void
             * @static
             */
            public static function relativeLink($target, $link)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                $instance->relativeLink($target, $link);
            }

            /**
             * Write the contents of a file, replacing it atomically if it already exists.
             *
             * @param  string $path
             * @param  string $content
             * @return void
             * @static
             */
            public static function replace($path, $content)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                $instance->replace($path, $content);
            }

            /**
             * Require the given file once.
             *
             * @param  string $path
             * @param  array  $data
             * @return mixed
             * @static
             */
            public static function requireOnce($path, $data = [])
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->requireOnce($path, $data);
            }

            /**
             * Get contents of a file with shared access.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function sharedGet($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->sharedGet($path);
            }

            /**
             * Get the file size of a given file.
             *
             * @param  string $path
             * @return int
             * @static
             */
            public static function size($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->size($path);
            }

            /**
             * Get the file type of a given file.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function type($path)
            {
                // @var \Illuminate\Filesystem\Filesystem $instance
                return $instance->type($path);
            }
        }
        /**
         * @see \Illuminate\Contracts\Auth\Access\Gate
         */
        class Gate
        {
            /**
             * Get all of the defined abilities.
             *
             * @return array
             * @static
             */
            public static function abilities()
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->abilities();
            }

            /**
             * Register a callback to run after all Gate checks.
             *
             * @param  callable                     $callback
             * @return \Illuminate\Auth\Access\Gate
             * @static
             */
            public static function after($callback)
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->after($callback);
            }

            /**
             * Determine if the given ability should be granted for the current user.
             *
             * @param  string      $ability
             * @param  array|mixed $arguments
             * @return bool
             * @static
             */
            public static function allows($ability, $arguments = [])
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->allows($ability, $arguments);
            }

            /**
             * Determine if any one of the given abilities should be granted for the current user.
             *
             * @param  \Illuminate\Auth\Access\iterable|string $abilities
             * @param  array|mixed                             $arguments
             * @return bool
             * @static
             */
            public static function any($abilities, $arguments = [])
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->any($abilities, $arguments);
            }

            /**
             * Determine if the given ability should be granted for the current user.
             *
             * @param  string                                         $ability
             * @param  array|mixed                                    $arguments
             * @throws \Illuminate\Auth\Access\AuthorizationException
             * @return \Illuminate\Auth\Access\Response
             * @static
             */
            public static function authorize($ability, $arguments = [])
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->authorize($ability, $arguments);
            }

            /**
             * Register a callback to run before all Gate checks.
             *
             * @param  callable                     $callback
             * @return \Illuminate\Auth\Access\Gate
             * @static
             */
            public static function before($callback)
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->before($callback);
            }

            /**
             * Determine if all of the given abilities should be granted for the current user.
             *
             * @param  \Illuminate\Auth\Access\iterable|string $abilities
             * @param  array|mixed                             $arguments
             * @return bool
             * @static
             */
            public static function check($abilities, $arguments = [])
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->check($abilities, $arguments);
            }

            /**
             * Define a new ability.
             *
             * @param  string                       $ability
             * @param  callable|string              $callback
             * @throws \InvalidArgumentException
             * @return \Illuminate\Auth\Access\Gate
             * @static
             */
            public static function define($ability, $callback)
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->define($ability, $callback);
            }

            /**
             * Determine if the given ability should be denied for the current user.
             *
             * @param  string      $ability
             * @param  array|mixed $arguments
             * @return bool
             * @static
             */
            public static function denies($ability, $arguments = [])
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->denies($ability, $arguments);
            }

            /**
             * Get a gate instance for the given user.
             *
             * @param  \Illuminate\Contracts\Auth\Authenticatable|mixed $user
             * @return static
             * @static
             */
            public static function forUser($user)
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->forUser($user);
            }

            /**
             * Get a policy instance for a given class.
             *
             * @param  object|string $class
             * @return mixed
             * @static
             */
            public static function getPolicyFor($class)
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->getPolicyFor($class);
            }

            /**
             * Specify a callback to be used to guess policy names.
             *
             * @param  callable                     $callback
             * @return \Illuminate\Auth\Access\Gate
             * @static
             */
            public static function guessPolicyNamesUsing($callback)
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->guessPolicyNamesUsing($callback);
            }

            /**
             * Determine if a given ability has been defined.
             *
             * @param  array|string $ability
             * @return bool
             * @static
             */
            public static function has($ability)
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->has($ability);
            }

            /**
             * Inspect the user for the given ability.
             *
             * @param  string                           $ability
             * @param  array|mixed                      $arguments
             * @return \Illuminate\Auth\Access\Response
             * @static
             */
            public static function inspect($ability, $arguments = [])
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->inspect($ability, $arguments);
            }

            /**
             * Determine if all of the given abilities should be denied for the current user.
             *
             * @param  \Illuminate\Auth\Access\iterable|string $abilities
             * @param  array|mixed                             $arguments
             * @return bool
             * @static
             */
            public static function none($abilities, $arguments = [])
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->none($abilities, $arguments);
            }

            /**
             * Get all of the defined policies.
             *
             * @return array
             * @static
             */
            public static function policies()
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->policies();
            }

            /**
             * Define a policy class for a given class type.
             *
             * @param  string                       $class
             * @param  string                       $policy
             * @return \Illuminate\Auth\Access\Gate
             * @static
             */
            public static function policy($class, $policy)
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->policy($class, $policy);
            }

            /**
             * Get the raw result from the authorization callback.
             *
             * @param  string                                         $ability
             * @param  array|mixed                                    $arguments
             * @throws \Illuminate\Auth\Access\AuthorizationException
             * @return mixed
             * @static
             */
            public static function raw($ability, $arguments = [])
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->raw($ability, $arguments);
            }

            /**
             * Build a policy class instance of the given type.
             *
             * @param  object|string                                              $class
             * @throws \Illuminate\Contracts\Container\BindingResolutionException
             * @return mixed
             * @static
             */
            public static function resolvePolicy($class)
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->resolvePolicy($class);
            }

            /**
             * Define abilities for a resource.
             *
             * @param  string                       $name
             * @param  string                       $class
             * @param  array|null                   $abilities
             * @return \Illuminate\Auth\Access\Gate
             * @static
             */
            public static function resource($name, $class, $abilities = null)
            {
                // @var \Illuminate\Auth\Access\Gate $instance
                return $instance->resource($name, $class, $abilities);
            }
        }
        /**
         * @see \Illuminate\Hashing\HashManager
         */
        class Hash
        {
            /**
             * Check the given plain value against a hash.
             *
             * @param  string $value
             * @param  string $hashedValue
             * @param  array  $options
             * @return bool
             * @static
             */
            public static function check($value, $hashedValue, $options = [])
            {
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->check($value, $hashedValue, $options);
            }

            /**
             * Create an instance of the Argon2id hash Driver.
             *
             * @return \Illuminate\Hashing\Argon2IdHasher
             * @static
             */
            public static function createArgon2idDriver()
            {
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->createArgon2idDriver();
            }

            /**
             * Create an instance of the Argon2i hash Driver.
             *
             * @return \Illuminate\Hashing\ArgonHasher
             * @static
             */
            public static function createArgonDriver()
            {
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->createArgonDriver();
            }

            /**
             * Create an instance of the Bcrypt hash Driver.
             *
             * @return \Illuminate\Hashing\BcryptHasher
             * @static
             */
            public static function createBcryptDriver()
            {
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->createBcryptDriver();
            }

            /**
             * Get a driver instance.
             *
             * @param  string|null               $driver
             * @throws \InvalidArgumentException
             * @return mixed
             * @static
             */
            public static function driver($driver = null)
            {            //Method inherited from \Illuminate\Support\Manager
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->driver($driver);
            }

            /**
             * Register a custom driver creator Closure.
             *
             * @param  string                          $driver
             * @param  \Closure                        $callback
             * @return \Illuminate\Hashing\HashManager
             * @static
             */
            public static function extend($driver, $callback)
            {            //Method inherited from \Illuminate\Support\Manager
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->extend($driver, $callback);
            }

            /**
             * Get the default driver name.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Get all of the created "drivers".
             *
             * @return array
             * @static
             */
            public static function getDrivers()
            {            //Method inherited from \Illuminate\Support\Manager
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->getDrivers();
            }

            /**
             * Get information about the given hashed value.
             *
             * @param  string $hashedValue
             * @return array
             * @static
             */
            public static function info($hashedValue)
            {
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->info($hashedValue);
            }

            /**
             * Hash the given value.
             *
             * @param  string $value
             * @param  array  $options
             * @return string
             * @static
             */
            public static function make($value, $options = [])
            {
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->make($value, $options);
            }

            /**
             * Check if the given hash has been hashed using the given options.
             *
             * @param  string $hashedValue
             * @param  array  $options
             * @return bool
             * @static
             */
            public static function needsRehash($hashedValue, $options = [])
            {
                // @var \Illuminate\Hashing\HashManager $instance
                return $instance->needsRehash($hashedValue, $options);
            }
        }
        /**
         * @see \Illuminate\Translation\Translator
         */
        class Lang
        {
            /**
             * Add a new JSON path to the loader.
             *
             * @param  string $path
             * @return void
             * @static
             */
            public static function addJsonPath($path)
            {
                // @var \Illuminate\Translation\Translator $instance
                $instance->addJsonPath($path);
            }

            /**
             * Add translation lines to the given locale.
             *
             * @param  array  $lines
             * @param  string $locale
             * @param  string $namespace
             * @return void
             * @static
             */
            public static function addLines($lines, $locale, $namespace = '*')
            {
                // @var \Illuminate\Translation\Translator $instance
                $instance->addLines($lines, $locale, $namespace);
            }

            /**
             * Add a new namespace to the loader.
             *
             * @param  string $namespace
             * @param  string $hint
             * @return void
             * @static
             */
            public static function addNamespace($namespace, $hint)
            {
                // @var \Illuminate\Translation\Translator $instance
                $instance->addNamespace($namespace, $hint);
            }

            /**
             * Get a translation according to an integer value.
             *
             * @param  string               $key
             * @param  array|\Countable|int $number
             * @param  array                $replace
             * @param  string|null          $locale
             * @return string
             * @static
             */
            public static function choice($key, $number, $replace = [], $locale = null)
            {
                // @var \Illuminate\Translation\Translator $instance
                return $instance->choice($key, $number, $replace, $locale);
            }

            /**
             * Get the translation for the given key.
             *
             * @param  string       $key
             * @param  array        $replace
             * @param  string|null  $locale
             * @param  bool         $fallback
             * @return array|string
             * @static
             */
            public static function get($key, $replace = [], $locale = null, $fallback = true)
            {
                // @var \Illuminate\Translation\Translator $instance
                return $instance->get($key, $replace, $locale, $fallback);
            }

            /**
             * Get the fallback locale being used.
             *
             * @return string
             * @static
             */
            public static function getFallback()
            {
                // @var \Illuminate\Translation\Translator $instance
                return $instance->getFallback();
            }

            /**
             * Get the language line loader implementation.
             *
             * @return \Illuminate\Contracts\Translation\Loader
             * @static
             */
            public static function getLoader()
            {
                // @var \Illuminate\Translation\Translator $instance
                return $instance->getLoader();
            }

            /**
             * Get the default locale being used.
             *
             * @return string
             * @static
             */
            public static function getLocale()
            {
                // @var \Illuminate\Translation\Translator $instance
                return $instance->getLocale();
            }

            /**
             * Get the message selector instance.
             *
             * @return \Illuminate\Translation\MessageSelector
             * @static
             */
            public static function getSelector()
            {
                // @var \Illuminate\Translation\Translator $instance
                return $instance->getSelector();
            }

            /**
             * Determine if a translation exists.
             *
             * @param  string      $key
             * @param  string|null $locale
             * @param  bool        $fallback
             * @return bool
             * @static
             */
            public static function has($key, $locale = null, $fallback = true)
            {
                // @var \Illuminate\Translation\Translator $instance
                return $instance->has($key, $locale, $fallback);
            }

            /**
             * Determine if a translation exists for a given locale.
             *
             * @param  string      $key
             * @param  string|null $locale
             * @return bool
             * @static
             */
            public static function hasForLocale($key, $locale = null)
            {
                // @var \Illuminate\Translation\Translator $instance
                return $instance->hasForLocale($key, $locale);
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Translation\Translator::hasMacro($name);
            }

            /**
             * Load the specified language group.
             *
             * @param  string $namespace
             * @param  string $group
             * @param  string $locale
             * @return void
             * @static
             */
            public static function load($namespace, $group, $locale)
            {
                // @var \Illuminate\Translation\Translator $instance
                $instance->load($namespace, $group, $locale);
            }

            /**
             * Get the default locale being used.
             *
             * @return string
             * @static
             */
            public static function locale()
            {
                // @var \Illuminate\Translation\Translator $instance
                return $instance->locale();
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Translation\Translator::macro($name, $macro);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Translation\Translator::mixin($mixin, $replace);
            }

            /**
             * Parse a key into namespace, group, and item.
             *
             * @param  string $key
             * @return array
             * @static
             */
            public static function parseKey($key)
            {
                // @var \Illuminate\Translation\Translator $instance
                return $instance->parseKey($key);
            }

            /**
             * Set the fallback locale being used.
             *
             * @param  string $fallback
             * @return void
             * @static
             */
            public static function setFallback($fallback)
            {
                // @var \Illuminate\Translation\Translator $instance
                $instance->setFallback($fallback);
            }

            /**
             * Set the loaded translation groups.
             *
             * @param  array $loaded
             * @return void
             * @static
             */
            public static function setLoaded($loaded)
            {
                // @var \Illuminate\Translation\Translator $instance
                $instance->setLoaded($loaded);
            }

            /**
             * Set the default locale.
             *
             * @param  string $locale
             * @return void
             * @static
             */
            public static function setLocale($locale)
            {
                // @var \Illuminate\Translation\Translator $instance
                $instance->setLocale($locale);
            }

            /**
             * Set the parsed value of a key.
             *
             * @param  string $key
             * @param  array  $parsed
             * @return void
             * @static
             */
            public static function setParsedKey($key, $parsed)
            {            //Method inherited from \Illuminate\Support\NamespacedItemResolver
                // @var \Illuminate\Translation\Translator $instance
                $instance->setParsedKey($key, $parsed);
            }

            /**
             * Set the message selector instance.
             *
             * @param  \Illuminate\Translation\MessageSelector $selector
             * @return void
             * @static
             */
            public static function setSelector($selector)
            {
                // @var \Illuminate\Translation\Translator $instance
                $instance->setSelector($selector);
            }
        }
        /**
         * @method static void write(string $level, string $message, array $context = [])
         * @method static void listen(\Closure $callback)
         * @see \Illuminate\Log\Logger
         */
        class Log
        {
            /**
             * Action must be taken immediately.
             *
             * Example: Entire website down, database unavailable, etc. This should
             * trigger the SMS alerts and wake you up.
             *
             * @param  string $message
             * @param  array  $context
             * @return void
             * @static
             */
            public static function alert($message, $context = [])
            {
                // @var \Illuminate\Log\LogManager $instance
                $instance->alert($message, $context);
            }

            /**
             * Get a log channel instance.
             *
             * @param  string|null              $channel
             * @return \Psr\Log\LoggerInterface
             * @static
             */
            public static function channel($channel = null)
            {
                // @var \Illuminate\Log\LogManager $instance
                return $instance->channel($channel);
            }

            /**
             * Critical conditions.
             *
             * Example: Application component unavailable, unexpected exception.
             *
             * @param  string $message
             * @param  array  $context
             * @return void
             * @static
             */
            public static function critical($message, $context = [])
            {
                // @var \Illuminate\Log\LogManager $instance
                $instance->critical($message, $context);
            }

            /**
             * Detailed debug information.
             *
             * @param  string $message
             * @param  array  $context
             * @return void
             * @static
             */
            public static function debug($message, $context = [])
            {
                // @var \Illuminate\Log\LogManager $instance
                $instance->debug($message, $context);
            }

            /**
             * Get a log driver instance.
             *
             * @param  string|null              $driver
             * @return \Psr\Log\LoggerInterface
             * @static
             */
            public static function driver($driver = null)
            {
                // @var \Illuminate\Log\LogManager $instance
                return $instance->driver($driver);
            }

            /**
             * System is unusable.
             *
             * @param  string $message
             * @param  array  $context
             * @return void
             * @static
             */
            public static function emergency($message, $context = [])
            {
                // @var \Illuminate\Log\LogManager $instance
                $instance->emergency($message, $context);
            }

            /**
             * Runtime errors that do not require immediate action but should typically
             * be logged and monitored.
             *
             * @param  string $message
             * @param  array  $context
             * @return void
             * @static
             */
            public static function error($message, $context = [])
            {
                // @var \Illuminate\Log\LogManager $instance
                $instance->error($message, $context);
            }

            /**
             * Register a custom driver creator Closure.
             *
             * @param  string                     $driver
             * @param  \Closure                   $callback
             * @return \Illuminate\Log\LogManager
             * @static
             */
            public static function extend($driver, $callback)
            {
                // @var \Illuminate\Log\LogManager $instance
                return $instance->extend($driver, $callback);
            }

            /**
             * Unset the given channel instance.
             *
             * @param  string|null                $driver
             * @return \Illuminate\Log\LogManager
             * @static
             */
            public static function forgetChannel($driver = null)
            {
                // @var \Illuminate\Log\LogManager $instance
                return $instance->forgetChannel($driver);
            }

            /**
             * @return array
             * @static
             */
            public static function getChannels()
            {
                // @var \Illuminate\Log\LogManager $instance
                return $instance->getChannels();
            }

            /**
             * Get the default log driver name.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Log\LogManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Interesting events.
             *
             * Example: User logs in, SQL logs.
             *
             * @param  string $message
             * @param  array  $context
             * @return void
             * @static
             */
            public static function info($message, $context = [])
            {
                // @var \Illuminate\Log\LogManager $instance
                $instance->info($message, $context);
            }

            /**
             * Logs with an arbitrary level.
             *
             * @param  mixed  $level
             * @param  string $message
             * @param  array  $context
             * @return void
             * @static
             */
            public static function log($level, $message, $context = [])
            {
                // @var \Illuminate\Log\LogManager $instance
                $instance->log($level, $message, $context);
            }

            /**
             * Normal but significant events.
             *
             * @param  string $message
             * @param  array  $context
             * @return void
             * @static
             */
            public static function notice($message, $context = [])
            {
                // @var \Illuminate\Log\LogManager $instance
                $instance->notice($message, $context);
            }

            /**
             * Set the default log driver name.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function setDefaultDriver($name)
            {
                // @var \Illuminate\Log\LogManager $instance
                $instance->setDefaultDriver($name);
            }

            /**
             * Create a new, on-demand aggregate logger instance.
             *
             * @param  array                    $channels
             * @param  string|null              $channel
             * @return \Psr\Log\LoggerInterface
             * @static
             */
            public static function stack($channels, $channel = null)
            {
                // @var \Illuminate\Log\LogManager $instance
                return $instance->stack($channels, $channel);
            }

            /**
             * Exceptional occurrences that are not errors.
             *
             * Example: Use of deprecated APIs, poor use of an API, undesirable things
             * that are not necessarily wrong.
             *
             * @param  string $message
             * @param  array  $context
             * @return void
             * @static
             */
            public static function warning($message, $context = [])
            {
                // @var \Illuminate\Log\LogManager $instance
                $instance->warning($message, $context);
            }
        }
        /**
         * @method static mixed laterOn(string $queue, \DateInterval|\DateTimeInterface|int $delay, array|\Illuminate\Contracts\Mail\Mailable|string $view)
         * @method static mixed queueOn(string $queue, array|\Illuminate\Contracts\Mail\Mailable|string $view)
         * @method static void plain(string $view, array $data, $callback)
         * @method static void html(string $html, $callback)
         * @see \Illuminate\Mail\Mailer
         * @see \Illuminate\Support\Testing\Fakes\MailFake
         */
        class Mail
        {
            /**
             * Assert that no mailables were queued.
             *
             * @return void
             * @static
             */
            public static function assertNothingQueued()
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                $instance->assertNothingQueued();
            }

            /**
             * Assert that no mailables were sent.
             *
             * @return void
             * @static
             */
            public static function assertNothingSent()
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                $instance->assertNothingSent();
            }

            /**
             * Determine if a mailable was not queued based on a truth-test callback.
             *
             * @param  string        $mailable
             * @param  callable|null $callback
             * @return void
             * @static
             */
            public static function assertNotQueued($mailable, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                $instance->assertNotQueued($mailable, $callback);
            }

            /**
             * Determine if a mailable was not sent based on a truth-test callback.
             *
             * @param  string        $mailable
             * @param  callable|null $callback
             * @return void
             * @static
             */
            public static function assertNotSent($mailable, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                $instance->assertNotSent($mailable, $callback);
            }

            /**
             * Assert if a mailable was queued based on a truth-test callback.
             *
             * @param  \Closure|string   $mailable
             * @param  callable|int|null $callback
             * @return void
             * @static
             */
            public static function assertQueued($mailable, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                $instance->assertQueued($mailable, $callback);
            }

            /**
             * Assert if a mailable was sent based on a truth-test callback.
             *
             * @param  \Closure|string   $mailable
             * @param  callable|int|null $callback
             * @return void
             * @static
             */
            public static function assertSent($mailable, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                $instance->assertSent($mailable, $callback);
            }

            /**
             * Begin the process of mailing a mailable class instance.
             *
             * @param  mixed                        $users
             * @return \Illuminate\Mail\PendingMail
             * @static
             */
            public static function bcc($users)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                return $instance->bcc($users);
            }

            /**
             * Create a new transport instance.
             *
             * @param  array            $config
             * @return \Swift_Transport
             * @static
             */
            public static function createTransport($config)
            {
                // @var \Illuminate\Mail\MailManager $instance
                return $instance->createTransport($config);
            }

            /**
             * Get a mailer driver instance.
             *
             * @param  string|null             $driver
             * @return \Illuminate\Mail\Mailer
             * @static
             */
            public static function driver($driver = null)
            {
                // @var \Illuminate\Mail\MailManager $instance
                return $instance->driver($driver);
            }

            /**
             * Register a custom transport creator Closure.
             *
             * @param  string                       $driver
             * @param  \Closure                     $callback
             * @return \Illuminate\Mail\MailManager
             * @static
             */
            public static function extend($driver, $callback)
            {
                // @var \Illuminate\Mail\MailManager $instance
                return $instance->extend($driver, $callback);
            }

            /**
             * Get the array of failed recipients.
             *
             * @return array
             * @static
             */
            public static function failures()
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                return $instance->failures();
            }

            /**
             * Get the default mail driver name.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Mail\MailManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Determine if the given mailable has been queued.
             *
             * @param  string $mailable
             * @return bool
             * @static
             */
            public static function hasQueued($mailable)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                return $instance->hasQueued($mailable);
            }

            /**
             * Determine if the given mailable has been sent.
             *
             * @param  string $mailable
             * @return bool
             * @static
             */
            public static function hasSent($mailable)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                return $instance->hasSent($mailable);
            }

            /**
             * Queue a new e-mail message for sending after (n) seconds.
             *
             * @param  \DateInterval|\DateTimeInterface|int             $delay
             * @param  array|\Illuminate\Contracts\Mail\Mailable|string $view
             * @param  string|null                                      $queue
             * @return mixed
             * @static
             */
            public static function later($delay, $view, $queue = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                return $instance->later($delay, $view, $queue);
            }

            /**
             * Get a mailer instance by name.
             *
             * @param  string|null             $name
             * @return \Illuminate\Mail\Mailer
             * @static
             */
            public static function mailer($name = null)
            {
                // @var \Illuminate\Mail\MailManager $instance
                return $instance->mailer($name);
            }

            /**
             * Disconnect the given mailer and remove from local cache.
             *
             * @param  string|null $name
             * @return void
             * @static
             */
            public static function purge($name = null)
            {
                // @var \Illuminate\Mail\MailManager $instance
                $instance->purge($name);
            }

            /**
             * Queue a new e-mail message for sending.
             *
             * @param  array|\Illuminate\Contracts\Mail\Mailable|string $view
             * @param  string|null                                      $queue
             * @return mixed
             * @static
             */
            public static function queue($view, $queue = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                return $instance->queue($view, $queue);
            }

            /**
             * Get all of the queued mailables matching a truth-test callback.
             *
             * @param  string                         $mailable
             * @param  callable|null                  $callback
             * @return \Illuminate\Support\Collection
             * @static
             */
            public static function queued($mailable, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                return $instance->queued($mailable, $callback);
            }

            /**
             * Send a new message with only a raw text part.
             *
             * @param  string          $text
             * @param  \Closure|string $callback
             * @return void
             * @static
             */
            public static function raw($text, $callback)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                $instance->raw($text, $callback);
            }

            /**
             * Send a new message using a view.
             *
             * @param  array|\Illuminate\Contracts\Mail\Mailable|string $view
             * @param  array                                            $data
             * @param  \Closure|string|null                             $callback
             * @return void
             * @static
             */
            public static function send($view, $data = [], $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                $instance->send($view, $data, $callback);
            }

            /**
             * Get all of the mailables matching a truth-test callback.
             *
             * @param  string                         $mailable
             * @param  callable|null                  $callback
             * @return \Illuminate\Support\Collection
             * @static
             */
            public static function sent($mailable, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                return $instance->sent($mailable, $callback);
            }

            /**
             * Set the default mail driver name.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function setDefaultDriver($name)
            {
                // @var \Illuminate\Mail\MailManager $instance
                $instance->setDefaultDriver($name);
            }

            /**
             * Begin the process of mailing a mailable class instance.
             *
             * @param  mixed                        $users
             * @return \Illuminate\Mail\PendingMail
             * @static
             */
            public static function to($users)
            {
                // @var \Illuminate\Support\Testing\Fakes\MailFake $instance
                return $instance->to($users);
            }
        }
        /**
         * @see \Illuminate\Notifications\ChannelManager
         */
        class Notification
        {
            /**
             * Assert that no notifications were sent.
             *
             * @return void
             * @static
             */
            public static function assertNothingSent()
            {
                // @var \Illuminate\Support\Testing\Fakes\NotificationFake $instance
                $instance->assertNothingSent();
            }

            /**
             * Determine if a notification was sent based on a truth-test callback.
             *
             * @param  mixed           $notifiable
             * @param  \Closure|string $notification
             * @param  callable|null   $callback
             * @throws \Exception
             * @return void
             * @static
             */
            public static function assertNotSentTo($notifiable, $notification, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\NotificationFake $instance
                $instance->assertNotSentTo($notifiable, $notification, $callback);
            }

            /**
             * Assert if a notification was sent based on a truth-test callback.
             *
             * @param  mixed           $notifiable
             * @param  \Closure|string $notification
             * @param  callable|null   $callback
             * @throws \Exception
             * @return void
             * @static
             */
            public static function assertSentTo($notifiable, $notification, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\NotificationFake $instance
                $instance->assertSentTo($notifiable, $notification, $callback);
            }

            /**
             * Assert if a notification was sent a number of times.
             *
             * @param  mixed  $notifiable
             * @param  string $notification
             * @param  int    $times
             * @return void
             * @static
             */
            public static function assertSentToTimes($notifiable, $notification, $times = 1)
            {
                // @var \Illuminate\Support\Testing\Fakes\NotificationFake $instance
                $instance->assertSentToTimes($notifiable, $notification, $times);
            }

            /**
             * Assert the total amount of times a notification was sent.
             *
             * @param  int    $expectedCount
             * @param  string $notification
             * @return void
             * @static
             */
            public static function assertTimesSent($expectedCount, $notification)
            {
                // @var \Illuminate\Support\Testing\Fakes\NotificationFake $instance
                $instance->assertTimesSent($expectedCount, $notification);
            }

            /**
             * Get a channel instance.
             *
             * @param  string|null $name
             * @return mixed
             * @static
             */
            public static function channel($name = null)
            {
                // @var \Illuminate\Notifications\ChannelManager $instance
                return $instance->channel($name);
            }

            /**
             * Get the default channel driver name.
             *
             * @return string
             * @static
             */
            public static function deliversVia()
            {
                // @var \Illuminate\Notifications\ChannelManager $instance
                return $instance->deliversVia();
            }

            /**
             * Set the default channel driver name.
             *
             * @param  string $channel
             * @return void
             * @static
             */
            public static function deliverVia($channel)
            {
                // @var \Illuminate\Notifications\ChannelManager $instance
                $instance->deliverVia($channel);
            }

            /**
             * Get a driver instance.
             *
             * @param  string|null               $driver
             * @throws \InvalidArgumentException
             * @return mixed
             * @static
             */
            public static function driver($driver = null)
            {            //Method inherited from \Illuminate\Support\Manager
                // @var \Illuminate\Notifications\ChannelManager $instance
                return $instance->driver($driver);
            }

            /**
             * Register a custom driver creator Closure.
             *
             * @param  string                                   $driver
             * @param  \Closure                                 $callback
             * @return \Illuminate\Notifications\ChannelManager
             * @static
             */
            public static function extend($driver, $callback)
            {            //Method inherited from \Illuminate\Support\Manager
                // @var \Illuminate\Notifications\ChannelManager $instance
                return $instance->extend($driver, $callback);
            }

            /**
             * Get the default channel driver name.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Notifications\ChannelManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Get all of the created "drivers".
             *
             * @return array
             * @static
             */
            public static function getDrivers()
            {            //Method inherited from \Illuminate\Support\Manager
                // @var \Illuminate\Notifications\ChannelManager $instance
                return $instance->getDrivers();
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Support\Testing\Fakes\NotificationFake::hasMacro($name);
            }

            /**
             * Determine if there are more notifications left to inspect.
             *
             * @param  mixed  $notifiable
             * @param  string $notification
             * @return bool
             * @static
             */
            public static function hasSent($notifiable, $notification)
            {
                // @var \Illuminate\Support\Testing\Fakes\NotificationFake $instance
                return $instance->hasSent($notifiable, $notification);
            }

            /**
             * Set the locale of notifications.
             *
             * @param  string                                   $locale
             * @return \Illuminate\Notifications\ChannelManager
             * @static
             */
            public static function locale($locale)
            {
                // @var \Illuminate\Notifications\ChannelManager $instance
                return $instance->locale($locale);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Support\Testing\Fakes\NotificationFake::macro($name, $macro);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Support\Testing\Fakes\NotificationFake::mixin($mixin, $replace);
            }

            /**
             * Send the given notification to the given notifiable entities.
             *
             * @param  array|\Illuminate\Support\Collection|mixed $notifiables
             * @param  mixed                                      $notification
             * @return void
             * @static
             */
            public static function send($notifiables, $notification)
            {
                // @var \Illuminate\Notifications\ChannelManager $instance
                $instance->send($notifiables, $notification);
            }

            /**
             * Send the given notification immediately.
             *
             * @param  array|\Illuminate\Support\Collection|mixed $notifiables
             * @param  mixed                                      $notification
             * @param  array|null                                 $channels
             * @return void
             * @static
             */
            public static function sendNow($notifiables, $notification, $channels = null)
            {
                // @var \Illuminate\Notifications\ChannelManager $instance
                $instance->sendNow($notifiables, $notification, $channels);
            }

            /**
             * Get all of the notifications matching a truth-test callback.
             *
             * @param  mixed                          $notifiable
             * @param  string                         $notification
             * @param  callable|null                  $callback
             * @return \Illuminate\Support\Collection
             * @static
             */
            public static function sent($notifiable, $notification, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\NotificationFake $instance
                return $instance->sent($notifiable, $notification, $callback);
            }
        }
        /**
         * @method static mixed reset(array $credentials, \Closure $callback)
         * @method static string sendResetLink(array $credentials, \Closure $callback = null)
         * @method static \Illuminate\Contracts\Auth\CanResetPassword getUser(array $credentials)
         * @method static string createToken(\Illuminate\Contracts\Auth\CanResetPassword $user)
         * @method static void deleteToken(\Illuminate\Contracts\Auth\CanResetPassword $user)
         * @method static bool tokenExists(\Illuminate\Contracts\Auth\CanResetPassword $user, string $token)
         * @method static \Illuminate\Auth\Passwords\TokenRepositoryInterface getRepository()
         * @see \Illuminate\Auth\Passwords\PasswordBroker
         */
        class Password
        {
            /**
             * Attempt to get the broker from the local cache.
             *
             * @param  string|null                               $name
             * @return \Illuminate\Contracts\Auth\PasswordBroker
             * @static
             */
            public static function broker($name = null)
            {
                // @var \Illuminate\Auth\Passwords\PasswordBrokerManager $instance
                return $instance->broker($name);
            }

            /**
             * Get the default password broker name.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Auth\Passwords\PasswordBrokerManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Set the default password broker name.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function setDefaultDriver($name)
            {
                // @var \Illuminate\Auth\Passwords\PasswordBrokerManager $instance
                $instance->setDefaultDriver($name);
            }
        }
        /**
         * @method static void popUsing(string $workerName, callable $callback)
         * @see \Illuminate\Queue\QueueManager
         * @see \Illuminate\Queue\Queue
         */
        class Queue
        {
            /**
             * Add a queue connection resolver.
             *
             * @param  string   $driver
             * @param  \Closure $resolver
             * @return void
             * @static
             */
            public static function addConnector($driver, $resolver)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                $instance->addConnector($driver, $resolver);
            }

            /**
             * Register an event listener for the after job event.
             *
             * @param  mixed $callback
             * @return void
             * @static
             */
            public static function after($callback)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                $instance->after($callback);
            }

            /**
             * Assert that no jobs were pushed.
             *
             * @return void
             * @static
             */
            public static function assertNothingPushed()
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                $instance->assertNothingPushed();
            }

            /**
             * Determine if a job was pushed based on a truth-test callback.
             *
             * @param  \Closure|string $job
             * @param  callable|null   $callback
             * @return void
             * @static
             */
            public static function assertNotPushed($job, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                $instance->assertNotPushed($job, $callback);
            }

            /**
             * Assert if a job was pushed based on a truth-test callback.
             *
             * @param  \Closure|string   $job
             * @param  callable|int|null $callback
             * @return void
             * @static
             */
            public static function assertPushed($job, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                $instance->assertPushed($job, $callback);
            }

            /**
             * Assert if a job was pushed based on a truth-test callback.
             *
             * @param  string          $queue
             * @param  \Closure|string $job
             * @param  callable|null   $callback
             * @return void
             * @static
             */
            public static function assertPushedOn($queue, $job, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                $instance->assertPushedOn($queue, $job, $callback);
            }

            /**
             * Assert if a job was pushed with chained jobs based on a truth-test callback.
             *
             * @param  string        $job
             * @param  array         $expectedChain
             * @param  callable|null $callback
             * @return void
             * @static
             */
            public static function assertPushedWithChain($job, $expectedChain = [], $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                $instance->assertPushedWithChain($job, $expectedChain, $callback);
            }

            /**
             * Assert if a job was pushed with an empty chain based on a truth-test callback.
             *
             * @param  string        $job
             * @param  callable|null $callback
             * @return void
             * @static
             */
            public static function assertPushedWithoutChain($job, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                $instance->assertPushedWithoutChain($job, $callback);
            }

            /**
             * Register an event listener for the before job event.
             *
             * @param  mixed $callback
             * @return void
             * @static
             */
            public static function before($callback)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                $instance->before($callback);
            }

            /**
             * Push an array of jobs onto the queue.
             *
             * @param  array       $jobs
             * @param  mixed       $data
             * @param  string|null $queue
             * @return mixed
             * @static
             */
            public static function bulk($jobs, $data = '', $queue = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->bulk($jobs, $data, $queue);
            }

            /**
             * Determine if the driver is connected.
             *
             * @param  string|null $name
             * @return bool
             * @static
             */
            public static function connected($name = null)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                return $instance->connected($name);
            }

            /**
             * Resolve a queue connection instance.
             *
             * @param  string|null                       $name
             * @return \Illuminate\Contracts\Queue\Queue
             * @static
             */
            public static function connection($name = null)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                return $instance->connection($name);
            }

            /**
             * Register a callback to be executed when creating job payloads.
             *
             * @param  callable|null $callback
             * @return void
             * @static
             */
            public static function createPayloadUsing($callback)
            {            //Method inherited from \Illuminate\Queue\Queue
                \Illuminate\Queue\SyncQueue::createPayloadUsing($callback);
            }

            /**
             * Register an event listener for the exception occurred job event.
             *
             * @param  mixed $callback
             * @return void
             * @static
             */
            public static function exceptionOccurred($callback)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                $instance->exceptionOccurred($callback);
            }

            /**
             * Add a queue connection resolver.
             *
             * @param  string   $driver
             * @param  \Closure $resolver
             * @return void
             * @static
             */
            public static function extend($driver, $resolver)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                $instance->extend($driver, $resolver);
            }

            /**
             * Register an event listener for the failed job event.
             *
             * @param  mixed $callback
             * @return void
             * @static
             */
            public static function failing($callback)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                $instance->failing($callback);
            }

            /**
             * Get the connection name for the queue.
             *
             * @return string
             * @static
             */
            public static function getConnectionName()
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->getConnectionName();
            }

            /**
             * Get the name of the default queue connection.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Queue\QueueManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Get the backoff for an object-based queue handler.
             *
             * @param  mixed $job
             * @return mixed
             * @static
             */
            public static function getJobBackoff($job)
            {            //Method inherited from \Illuminate\Queue\Queue
                // @var \Illuminate\Queue\SyncQueue $instance
                return $instance->getJobBackoff($job);
            }

            /**
             * Get the expiration timestamp for an object-based queue handler.
             *
             * @param  mixed $job
             * @return mixed
             * @static
             */
            public static function getJobExpiration($job)
            {            //Method inherited from \Illuminate\Queue\Queue
                // @var \Illuminate\Queue\SyncQueue $instance
                return $instance->getJobExpiration($job);
            }

            /**
             * Get the full name for the given connection.
             *
             * @param  string|null $connection
             * @return string
             * @static
             */
            public static function getName($connection = null)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                return $instance->getName($connection);
            }

            /**
             * Determine if there are any stored jobs for a given class.
             *
             * @param  string $job
             * @return bool
             * @static
             */
            public static function hasPushed($job)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->hasPushed($job);
            }

            /**
             * Push a new job onto the queue after a delay.
             *
             * @param  \DateInterval|\DateTimeInterface|int $delay
             * @param  string                               $job
             * @param  mixed                                $data
             * @param  string|null                          $queue
             * @return mixed
             * @static
             */
            public static function later($delay, $job, $data = '', $queue = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->later($delay, $job, $data, $queue);
            }

            /**
             * Push a new job onto the queue after a delay.
             *
             * @param  string                               $queue
             * @param  \DateInterval|\DateTimeInterface|int $delay
             * @param  string                               $job
             * @param  mixed                                $data
             * @return mixed
             * @static
             */
            public static function laterOn($queue, $delay, $job, $data = '')
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->laterOn($queue, $delay, $job, $data);
            }

            /**
             * Register an event listener for the daemon queue loop.
             *
             * @param  mixed $callback
             * @return void
             * @static
             */
            public static function looping($callback)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                $instance->looping($callback);
            }

            /**
             * Pop the next job off of the queue.
             *
             * @param  string|null                          $queue
             * @return \Illuminate\Contracts\Queue\Job|null
             * @static
             */
            public static function pop($queue = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->pop($queue);
            }

            /**
             * Push a new job onto the queue.
             *
             * @param  string      $job
             * @param  mixed       $data
             * @param  string|null $queue
             * @return mixed
             * @static
             */
            public static function push($job, $data = '', $queue = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->push($job, $data, $queue);
            }

            /**
             * Get all of the jobs matching a truth-test callback.
             *
             * @param  string                         $job
             * @param  callable|null                  $callback
             * @return \Illuminate\Support\Collection
             * @static
             */
            public static function pushed($job, $callback = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->pushed($job, $callback);
            }

            /**
             * Get the jobs that have been pushed.
             *
             * @return array
             * @static
             */
            public static function pushedJobs()
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->pushedJobs();
            }

            /**
             * Push a new job onto the queue.
             *
             * @param  string $queue
             * @param  string $job
             * @param  mixed  $data
             * @return mixed
             * @static
             */
            public static function pushOn($queue, $job, $data = '')
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->pushOn($queue, $job, $data);
            }

            /**
             * Push a raw payload onto the queue.
             *
             * @param  string      $payload
             * @param  string|null $queue
             * @param  array       $options
             * @return mixed
             * @static
             */
            public static function pushRaw($payload, $queue = null, $options = [])
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->pushRaw($payload, $queue, $options);
            }

            /**
             * Set the connection name for the queue.
             *
             * @param  string                                      $name
             * @return \Illuminate\Support\Testing\Fakes\QueueFake
             * @static
             */
            public static function setConnectionName($name)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->setConnectionName($name);
            }

            /**
             * Set the IoC container instance.
             *
             * @param  \Illuminate\Container\Container $container
             * @return void
             * @static
             */
            public static function setContainer($container)
            {            //Method inherited from \Illuminate\Queue\Queue
                // @var \Illuminate\Queue\SyncQueue $instance
                $instance->setContainer($container);
            }

            /**
             * Set the name of the default queue connection.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function setDefaultDriver($name)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                $instance->setDefaultDriver($name);
            }

            /**
             * Get the size of the queue.
             *
             * @param  string|null $queue
             * @return int
             * @static
             */
            public static function size($queue = null)
            {
                // @var \Illuminate\Support\Testing\Fakes\QueueFake $instance
                return $instance->size($queue);
            }

            /**
             * Register an event listener for the daemon queue stopping.
             *
             * @param  mixed $callback
             * @return void
             * @static
             */
            public static function stopping($callback)
            {
                // @var \Illuminate\Queue\QueueManager $instance
                $instance->stopping($callback);
            }
        }
        /**
         * @see \Illuminate\Routing\Redirector
         */
        class Redirect
        {
            /**
             * Create a new redirect response to a controller action.
             *
             * @param  array|string                      $action
             * @param  mixed                             $parameters
             * @param  int                               $status
             * @param  array                             $headers
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function action($action, $parameters = [], $status = 302, $headers = [])
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->action($action, $parameters, $status, $headers);
            }

            /**
             * Create a new redirect response to an external URL (no validation).
             *
             * @param  string                            $path
             * @param  int                               $status
             * @param  array                             $headers
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function away($path, $status = 302, $headers = [])
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->away($path, $status, $headers);
            }

            /**
             * Create a new redirect response to the previous location.
             *
             * @param  int                               $status
             * @param  array                             $headers
             * @param  mixed                             $fallback
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function back($status = 302, $headers = [], $fallback = false)
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->back($status, $headers, $fallback);
            }

            /**
             * Get the URL generator instance.
             *
             * @return \Illuminate\Routing\UrlGenerator
             * @static
             */
            public static function getUrlGenerator()
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->getUrlGenerator();
            }

            /**
             * Create a new redirect response, while putting the current URL in the session.
             *
             * @param  string                            $path
             * @param  int                               $status
             * @param  array                             $headers
             * @param  bool|null                         $secure
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function guest($path, $status = 302, $headers = [], $secure = null)
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->guest($path, $status, $headers, $secure);
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Routing\Redirector::hasMacro($name);
            }

            /**
             * Create a new redirect response to the "home" route.
             *
             * @param  int                               $status
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function home($status = 302)
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->home($status);
            }

            /**
             * Create a new redirect response to the previously intended location.
             *
             * @param  string                            $default
             * @param  int                               $status
             * @param  array                             $headers
             * @param  bool|null                         $secure
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function intended($default = '/', $status = 302, $headers = [], $secure = null)
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->intended($default, $status, $headers, $secure);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Routing\Redirector::macro($name, $macro);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Routing\Redirector::mixin($mixin, $replace);
            }

            /**
             * Create a new redirect response to the current URI.
             *
             * @param  int                               $status
             * @param  array                             $headers
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function refresh($status = 302, $headers = [])
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->refresh($status, $headers);
            }

            /**
             * Create a new redirect response to a named route.
             *
             * @param  string                            $route
             * @param  mixed                             $parameters
             * @param  int                               $status
             * @param  array                             $headers
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function route($route, $parameters = [], $status = 302, $headers = [])
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->route($route, $parameters, $status, $headers);
            }

            /**
             * Create a new redirect response to the given HTTPS path.
             *
             * @param  string                            $path
             * @param  int                               $status
             * @param  array                             $headers
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function secure($path, $status = 302, $headers = [])
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->secure($path, $status, $headers);
            }

            /**
             * Set the intended url.
             *
             * @param  string $url
             * @return void
             * @static
             */
            public static function setIntendedUrl($url)
            {
                // @var \Illuminate\Routing\Redirector $instance
                $instance->setIntendedUrl($url);
            }

            /**
             * Set the active session store.
             *
             * @param  \Illuminate\Session\Store $session
             * @return void
             * @static
             */
            public static function setSession($session)
            {
                // @var \Illuminate\Routing\Redirector $instance
                $instance->setSession($session);
            }

            /**
             * Create a new redirect response to a signed named route.
             *
             * @param  string                                    $route
             * @param  mixed                                     $parameters
             * @param  \DateInterval|\DateTimeInterface|int|null $expiration
             * @param  int                                       $status
             * @param  array                                     $headers
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function signedRoute($route, $parameters = [], $expiration = null, $status = 302, $headers = [])
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->signedRoute($route, $parameters, $expiration, $status, $headers);
            }

            /**
             * Create a new redirect response to a signed named route.
             *
             * @param  string                                    $route
             * @param  \DateInterval|\DateTimeInterface|int|null $expiration
             * @param  mixed                                     $parameters
             * @param  int                                       $status
             * @param  array                                     $headers
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function temporarySignedRoute($route, $expiration, $parameters = [], $status = 302, $headers = [])
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->temporarySignedRoute($route, $expiration, $parameters, $status, $headers);
            }

            /**
             * Create a new redirect response to the given path.
             *
             * @param  string                            $path
             * @param  int                               $status
             * @param  array                             $headers
             * @param  bool|null                         $secure
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function to($path, $status = 302, $headers = [], $secure = null)
            {
                // @var \Illuminate\Routing\Redirector $instance
                return $instance->to($path, $status, $headers, $secure);
            }
        }
        /**
         * @method static \Illuminate\Redis\Limiters\ConcurrencyLimiterBuilder funnel(string $name)
         * @method static \Illuminate\Redis\Limiters\DurationLimiterBuilder throttle(string $name)
         * @see \Illuminate\Redis\RedisManager
         * @see \Illuminate\Contracts\Redis\Factory
         */
        class Redis
        {
            /**
             * Get a Redis connection by name.
             *
             * @param  string|null                              $name
             * @return \Illuminate\Redis\Connections\Connection
             * @static
             */
            public static function connection($name = null)
            {
                // @var \Illuminate\Redis\RedisManager $instance
                return $instance->connection($name);
            }

            /**
             * Return all of the created connections.
             *
             * @return array
             * @static
             */
            public static function connections()
            {
                // @var \Illuminate\Redis\RedisManager $instance
                return $instance->connections();
            }

            /**
             * Disable the firing of Redis command events.
             *
             * @return void
             * @static
             */
            public static function disableEvents()
            {
                // @var \Illuminate\Redis\RedisManager $instance
                $instance->disableEvents();
            }

            /**
             * Enable the firing of Redis command events.
             *
             * @return void
             * @static
             */
            public static function enableEvents()
            {
                // @var \Illuminate\Redis\RedisManager $instance
                $instance->enableEvents();
            }

            /**
             * Register a custom driver creator Closure.
             *
             * @param  string                         $driver
             * @param  \Closure                       $callback
             * @return \Illuminate\Redis\RedisManager
             * @static
             */
            public static function extend($driver, $callback)
            {
                // @var \Illuminate\Redis\RedisManager $instance
                return $instance->extend($driver, $callback);
            }

            /**
             * Disconnect the given connection and remove from local cache.
             *
             * @param  string|null $name
             * @return void
             * @static
             */
            public static function purge($name = null)
            {
                // @var \Illuminate\Redis\RedisManager $instance
                $instance->purge($name);
            }

            /**
             * Resolve the given connection by name.
             *
             * @param  string|null                              $name
             * @throws \InvalidArgumentException
             * @return \Illuminate\Redis\Connections\Connection
             * @static
             */
            public static function resolve($name = null)
            {
                // @var \Illuminate\Redis\RedisManager $instance
                return $instance->resolve($name);
            }

            /**
             * Set the default driver.
             *
             * @param  string $driver
             * @return void
             * @static
             */
            public static function setDriver($driver)
            {
                // @var \Illuminate\Redis\RedisManager $instance
                $instance->setDriver($driver);
            }
        }
        /**
         * @method static mixed filterFiles(mixed $files)
         * @see \Illuminate\Http\Request
         */
        class Request
        {
            /**
             * Determines whether the current requests accepts a given content type.
             *
             * @param  array|string $contentTypes
             * @return bool
             * @static
             */
            public static function accepts($contentTypes)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->accepts($contentTypes);
            }

            /**
             * Determine if the current request accepts any content type.
             *
             * @return bool
             * @static
             */
            public static function acceptsAnyContentType()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->acceptsAnyContentType();
            }

            /**
             * Determines whether a request accepts HTML.
             *
             * @return bool
             * @static
             */
            public static function acceptsHtml()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->acceptsHtml();
            }

            /**
             * Determines whether a request accepts JSON.
             *
             * @return bool
             * @static
             */
            public static function acceptsJson()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->acceptsJson();
            }

            /**
             * Determine if the request is the result of an AJAX call.
             *
             * @return bool
             * @static
             */
            public static function ajax()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->ajax();
            }

            /**
             * Get all of the input and files for the request.
             *
             * @param  array|mixed|null $keys
             * @return array
             * @static
             */
            public static function all($keys = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->all($keys);
            }

            /**
             * Get an array of all of the files on the request.
             *
             * @return array
             * @static
             */
            public static function allFiles()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->allFiles();
            }

            /**
             * Determine if the request contains a non-empty value for any of the given inputs.
             *
             * @param  array|string $keys
             * @return bool
             * @static
             */
            public static function anyFilled($keys)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->anyFilled($keys);
            }

            /**
             * Get the bearer token from the request headers.
             *
             * @return string|null
             * @static
             */
            public static function bearerToken()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->bearerToken();
            }

            /**
             * Retrieve input as a boolean value.
             *
             * Returns true when value is "1", "true", "on", and "yes". Otherwise, returns false.
             *
             * @param  string|null $key
             * @param  bool        $default
             * @return bool
             * @static
             */
            public static function boolean($key = null, $default = false)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->boolean($key, $default);
            }

            /**
             * Create a new Illuminate HTTP request from server variables.
             *
             * @return static
             * @static
             */
            public static function capture()
            {
                return \Illuminate\Http\Request::capture();
            }

            /**
             * Retrieve a cookie from the request.
             *
             * @param  string|null       $key
             * @param  array|string|null $default
             * @return array|string|null
             * @static
             */
            public static function cookie($key = null, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->cookie($key, $default);
            }

            /**
             * Creates a Request based on a given URI and configuration.
             *
             * The information contained in the URI always take precedence
             * over the other information (server and parameters).
             *
             * @param  string               $uri        The URI
             * @param  string               $method     The HTTP method
             * @param  array                $parameters The query (GET) or request (POST) parameters
             * @param  array                $cookies    The request cookies ($_COOKIE)
             * @param  array                $files      The request files ($_FILES)
             * @param  array                $server     The server parameters ($_SERVER)
             * @param  resource|string|null $content    The raw body data
             * @return static
             * @static
             */
            public static function create($uri, $method = 'GET', $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::create($uri, $method, $parameters, $cookies, $files, $server, $content);
            }

            /**
             * Create a new request instance from the given Laravel request.
             *
             * @param  \Illuminate\Http\Request      $from
             * @param  \Illuminate\Http\Request|null $to
             * @return static
             * @static
             */
            public static function createFrom($from, $to = null)
            {
                return \Illuminate\Http\Request::createFrom($from, $to);
            }

            /**
             * Create an Illuminate request from a Symfony instance.
             *
             * @param  \Symfony\Component\HttpFoundation\Request $request
             * @return static
             * @static
             */
            public static function createFromBase($request)
            {
                return \Illuminate\Http\Request::createFromBase($request);
            }

            /**
             * Creates a new request with values from PHP's super globals.
             *
             * @return static
             * @static
             */
            public static function createFromGlobals()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::createFromGlobals();
            }

            /**
             * Dump the request items and end the script.
             *
             * @param  array|mixed $keys
             * @return void
             * @static
             */
            public static function dd(...$keys)
            {
                // @var \Illuminate\Http\Request $instance
                $instance->dd(...$keys);
            }

            /**
             * Get the current decoded path info for the request.
             *
             * @return string
             * @static
             */
            public static function decodedPath()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->decodedPath();
            }

            /**
             * Dump the items.
             *
             * @param  array                    $keys
             * @return \Illuminate\Http\Request
             * @static
             */
            public static function dump($keys = [])
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->dump($keys);
            }

            /**
             * Clones a request and overrides some of its parameters.
             *
             * @param  array  $query      The GET parameters
             * @param  array  $request    The POST parameters
             * @param  array  $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
             * @param  array  $cookies    The COOKIE parameters
             * @param  array  $files      The FILES parameters
             * @param  array  $server     The SERVER parameters
             * @return static
             * @static
             */
            public static function duplicate($query = null, $request = null, $attributes = null, $cookies = null, $files = null, $server = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->duplicate($query, $request, $attributes, $cookies, $files, $server);
            }

            /**
             * Enables support for the _method request parameter to determine the intended HTTP method.
             *
             * Be warned that enabling this feature might lead to CSRF issues in your code.
             * Check that you are using CSRF tokens when required.
             * If the HTTP method parameter override is enabled, an html-form with method "POST" can be altered
             * and used to send a "PUT" or "DELETE" request via the _method request parameter.
             * If these methods are not protected against CSRF, this presents a possible vulnerability.
             *
             * The HTTP method can only be overridden when the real HTTP method is POST.
             *
             * @static
             */
            public static function enableHttpMethodParameterOverride()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::enableHttpMethodParameterOverride();
            }

            /**
             * Get all of the input except for a specified array of items.
             *
             * @param  array|mixed $keys
             * @return array
             * @static
             */
            public static function except($keys)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->except($keys);
            }

            /**
             * Determine if the request contains a given input item key.
             *
             * @param  array|string $key
             * @return bool
             * @static
             */
            public static function exists($key)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->exists($key);
            }

            /**
             * Determine if the current request probably expects a JSON response.
             *
             * @return bool
             * @static
             */
            public static function expectsJson()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->expectsJson();
            }

            /**
             * Retrieve a file from the request.
             *
             * @param  string|null                                                              $key
             * @param  mixed                                                                    $default
             * @return array|\Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|null
             * @static
             */
            public static function file($key = null, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->file($key, $default);
            }

            /**
             * Determine if the request contains a non-empty value for an input item.
             *
             * @param  array|string $key
             * @return bool
             * @static
             */
            public static function filled($key)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->filled($key);
            }

            /**
             * Get a unique fingerprint for the request / route / IP address.
             *
             * @throws \RuntimeException
             * @return string
             * @static
             */
            public static function fingerprint()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->fingerprint();
            }

            /**
             * Flash the input for the current request to the session.
             *
             * @return void
             * @static
             */
            public static function flash()
            {
                // @var \Illuminate\Http\Request $instance
                $instance->flash();
            }

            /**
             * Flash only some of the input to the session.
             *
             * @param  array|mixed $keys
             * @return void
             * @static
             */
            public static function flashExcept($keys)
            {
                // @var \Illuminate\Http\Request $instance
                $instance->flashExcept($keys);
            }

            /**
             * Flash only some of the input to the session.
             *
             * @param  array|mixed $keys
             * @return void
             * @static
             */
            public static function flashOnly($keys)
            {
                // @var \Illuminate\Http\Request $instance
                $instance->flashOnly($keys);
            }

            /**
             * Flush all of the old input from the session.
             *
             * @return void
             * @static
             */
            public static function flush()
            {
                // @var \Illuminate\Http\Request $instance
                $instance->flush();
            }

            /**
             * Get the data format expected in the response.
             *
             * @param  string $default
             * @return string
             * @static
             */
            public static function format($default = 'html')
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->format($default);
            }

            /**
             * Get the full URL for the request.
             *
             * @return string
             * @static
             */
            public static function fullUrl()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->fullUrl();
            }

            /**
             * Determine if the current request URL and query string match a pattern.
             *
             * @param  mixed $patterns
             * @return bool
             * @static
             */
            public static function fullUrlIs(...$patterns)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->fullUrlIs(...$patterns);
            }

            /**
             * Get the full URL for the request with the added query string parameters.
             *
             * @param  array  $query
             * @return string
             * @static
             */
            public static function fullUrlWithQuery($query)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->fullUrlWithQuery($query);
            }

            /**
             * This method belongs to Symfony HttpFoundation and is not usually needed when using Laravel.
             *
             * Instead, you may use the "input" method.
             *
             * @param  string $key
             * @param  mixed  $default
             * @return mixed
             * @static
             */
            public static function get($key, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->get($key, $default);
            }

            /**
             * Gets a list of content types acceptable by the client browser.
             *
             * @return array List of content types in preferable order
             * @static
             */
            public static function getAcceptableContentTypes()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getAcceptableContentTypes();
            }

            /**
             * Returns the root path from which this request is executed.
             *
             * Suppose that an index.php file instantiates this request object:
             *
             *  * http://localhost/index.php         returns an empty string
             *  * http://localhost/index.php/page    returns an empty string
             *  * http://localhost/web/index.php     returns '/web'
             *  * http://localhost/we%20b/index.php  returns '/we%20b'
             *
             * @return string The raw path (i.e. not urldecoded)
             * @static
             */
            public static function getBasePath()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getBasePath();
            }

            /**
             * Returns the root URL from which this request is executed.
             *
             * The base URL never ends with a /.
             *
             * This is similar to getBasePath(), except that it also includes the
             * script filename (e.g. index.php) if one exists.
             *
             * @return string The raw URL (i.e. not urldecoded)
             * @static
             */
            public static function getBaseUrl()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getBaseUrl();
            }

            /**
             * Gets a list of charsets acceptable by the client browser.
             *
             * @return array List of charsets in preferable order
             * @static
             */
            public static function getCharsets()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getCharsets();
            }

            /**
             * Returns the client IP address.
             *
             * This method can read the client IP address from the "X-Forwarded-For" header
             * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
             * header value is a comma+space separated list of IP addresses, the left-most
             * being the original client, and each successive proxy that passed the request
             * adding the IP address where it received the request from.
             *
             * If your reverse proxy uses a different header name than "X-Forwarded-For",
             * ("Client-Ip" for instance), configure it via the $trustedHeaderSet
             * argument of the Request::setTrustedProxies() method instead.
             *
             * @return string|null The client IP address
             * @see getClientIps()
             * @see https://wikipedia.org/wiki/X-Forwarded-For
             * @static
             */
            public static function getClientIp()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getClientIp();
            }

            /**
             * Returns the client IP addresses.
             *
             * In the returned array the most trusted IP address is first, and the
             * least trusted one last. The "real" client IP address is the last one,
             * but this is also the least trusted one. Trusted proxies are stripped.
             *
             * Use this method carefully; you should use getClientIp() instead.
             *
             * @return array The client IP addresses
             * @see getClientIp()
             * @static
             */
            public static function getClientIps()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getClientIps();
            }

            /**
             * Returns the request body content.
             *
             * @param  bool            $asResource If true, a resource will be returned
             * @return resource|string The request body content or a resource to read the body stream
             * @static
             */
            public static function getContent($asResource = false)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getContent($asResource);
            }

            /**
             * Gets the format associated with the request.
             *
             * @return string|null The format (null if no content type is present)
             * @static
             */
            public static function getContentType()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getContentType();
            }

            /**
             * Get the default locale.
             *
             * @return string
             * @static
             */
            public static function getDefaultLocale()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getDefaultLocale();
            }

            /**
             * Gets a list of encodings acceptable by the client browser.
             *
             * @return array List of encodings in preferable order
             * @static
             */
            public static function getEncodings()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getEncodings();
            }

            /**
             * Gets the Etags.
             *
             * @return array The entity tags
             * @static
             */
            public static function getETags()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getETags();
            }

            /**
             * Gets the format associated with the mime type.
             *
             * @static
             * @param  mixed       $mimeType
             * @return string|null The format (null if not found)
             */
            public static function getFormat($mimeType)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getFormat($mimeType);
            }

            /**
             * Returns the host name.
             *
             * This method can read the client host name from the "X-Forwarded-Host" header
             * when trusted proxies were set via "setTrustedProxies()".
             *
             * The "X-Forwarded-Host" header must contain the client host name.
             *
             * @throws SuspiciousOperationException when the host name is invalid or not trusted
             * @return string
             * @static
             */
            public static function getHost()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getHost();
            }

            /**
             * Returns the HTTP host being requested.
             *
             * The port name will be appended to the host if it's non-standard.
             *
             * @return string
             * @static
             */
            public static function getHttpHost()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getHttpHost();
            }

            /**
             * Checks whether support for the _method request parameter is enabled.
             *
             * @return bool True when the _method request parameter is enabled, false otherwise
             * @static
             */
            public static function getHttpMethodParameterOverride()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::getHttpMethodParameterOverride();
            }

            /**
             * Gets a list of languages acceptable by the client browser.
             *
             * @return array Languages ordered in the user browser preferences
             * @static
             */
            public static function getLanguages()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getLanguages();
            }

            /**
             * Get the locale.
             *
             * @return string
             * @static
             */
            public static function getLocale()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getLocale();
            }

            /**
             * Gets the request "intended" method.
             *
             * If the X-HTTP-Method-Override header is set, and if the method is a POST,
             * then it is used to determine the "real" intended HTTP method.
             *
             * The _method request parameter can also be used to determine the HTTP method,
             * but only if enableHttpMethodParameterOverride() has been called.
             *
             * The method is always an uppercased string.
             *
             * @return string The request method
             * @see getRealMethod()
             * @static
             */
            public static function getMethod()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getMethod();
            }

            /**
             * Gets the mime type associated with the format.
             *
             * @static
             * @param  mixed       $format
             * @return string|null The associated mime type (null if not found)
             */
            public static function getMimeType($format)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getMimeType($format);
            }

            /**
             * Gets the mime types associated with the format.
             *
             * @static
             * @param  mixed $format
             * @return array The associated mime types
             */
            public static function getMimeTypes($format)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::getMimeTypes($format);
            }

            /**
             * Returns the password.
             *
             * @return string|null
             * @static
             */
            public static function getPassword()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getPassword();
            }

            /**
             * Returns the path being requested relative to the executed script.
             *
             * The path info always starts with a /.
             *
             * Suppose this request is instantiated from /mysite on localhost:
             *
             *  * http://localhost/mysite              returns an empty string
             *  * http://localhost/mysite/about        returns '/about'
             *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
             *  * http://localhost/mysite/about?var=1  returns '/about'
             *
             * @return string The raw path (i.e. not urldecoded)
             * @static
             */
            public static function getPathInfo()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getPathInfo();
            }

            /**
             * Returns the port on which the request is made.
             *
             * This method can read the client port from the "X-Forwarded-Port" header
             * when trusted proxies were set via "setTrustedProxies()".
             *
             * The "X-Forwarded-Port" header must contain the client port.
             *
             * @return int|string can be a string if fetched from the server bag
             * @static
             */
            public static function getPort()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getPort();
            }

            /**
             * Gets the preferred format for the response by inspecting, in the following order:
             *   * the request format set using setRequestFormat;
             *   * the values of the Accept HTTP header.
             *
             * Note that if you use this method, you should send the "Vary: Accept" header
             * in the response to prevent any issues with intermediary HTTP caches.
             *
             * @static
             * @param mixed $default
             */
            public static function getPreferredFormat($default = 'html')
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getPreferredFormat($default);
            }

            /**
             * Returns the preferred language.
             *
             * @param  string[]    $locales An array of ordered available locales
             * @return string|null The preferred locale
             * @static
             */
            public static function getPreferredLanguage($locales = null)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getPreferredLanguage($locales);
            }

            /**
             * Returns the protocol version.
             *
             * If the application is behind a proxy, the protocol version used in the
             * requests between the client and the proxy and between the proxy and the
             * server might be different. This returns the former (from the "Via" header)
             * if the proxy is trusted (see "setTrustedProxies()"), otherwise it returns
             * the latter (from the "SERVER_PROTOCOL" server parameter).
             *
             * @return string
             * @static
             */
            public static function getProtocolVersion()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getProtocolVersion();
            }

            /**
             * Generates the normalized query string for the Request.
             *
             * It builds a normalized query string, where keys/value pairs are alphabetized
             * and have consistent escaping.
             *
             * @return string|null A normalized query string for the Request
             * @static
             */
            public static function getQueryString()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getQueryString();
            }

            /**
             * Gets the "real" request method.
             *
             * @return string The request method
             * @see getMethod()
             * @static
             */
            public static function getRealMethod()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getRealMethod();
            }

            /**
             * Returns the path as relative reference from the current Request path.
             *
             * Only the URIs path component (no schema, host etc.) is relevant and must be given.
             * Both paths must be absolute and not contain relative parts.
             * Relative URLs from one resource to another are useful when generating self-contained downloadable document archives.
             * Furthermore, they can be used to reduce the link size in documents.
             *
             * Example target paths, given a base path of "/a/b/c/d":
             * - "/a/b/c/d"     -> ""
             * - "/a/b/c/"      -> "./"
             * - "/a/b/"        -> "../"
             * - "/a/b/c/other" -> "other"
             * - "/a/x/y"       -> "../../x/y"
             *
             * @static
             * @param  mixed  $path
             * @return string The relative target path
             */
            public static function getRelativeUriForPath($path)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getRelativeUriForPath($path);
            }

            /**
             * Gets the request format.
             *
             * Here is the process to determine the format:
             *
             *  * format defined by the user (with setRequestFormat())
             *  * _format request attribute
             *  * $default
             *
             * @see getPreferredFormat
             * @static
             * @param  mixed       $default
             * @return string|null The request format
             */
            public static function getRequestFormat($default = 'html')
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getRequestFormat($default);
            }

            /**
             * Returns the requested URI (path and query string).
             *
             * @return string The raw URI (i.e. not URI decoded)
             * @static
             */
            public static function getRequestUri()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getRequestUri();
            }

            /**
             * Get the route resolver callback.
             *
             * @return \Closure
             * @static
             */
            public static function getRouteResolver()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->getRouteResolver();
            }

            /**
             * Gets the request's scheme.
             *
             * @return string
             * @static
             */
            public static function getScheme()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getScheme();
            }

            /**
             * Gets the scheme and HTTP host.
             *
             * If the URL was called with basic authentication, the user
             * and the password are not added to the generated string.
             *
             * @return string The scheme and HTTP host
             * @static
             */
            public static function getSchemeAndHttpHost()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getSchemeAndHttpHost();
            }

            /**
             * Returns current script name.
             *
             * @return string
             * @static
             */
            public static function getScriptName()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getScriptName();
            }

            /**
             * Get the session associated with the request.
             *
             * @return \Illuminate\Session\Store|null
             * @static
             */
            public static function getSession()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->getSession();
            }

            /**
             * Gets the set of trusted headers from trusted proxies.
             *
             * @return int A bit field of Request::HEADER_* that defines which headers are trusted from your proxies
             * @static
             */
            public static function getTrustedHeaderSet()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::getTrustedHeaderSet();
            }

            /**
             * Gets the list of trusted host patterns.
             *
             * @return array An array of trusted host patterns
             * @static
             */
            public static function getTrustedHosts()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::getTrustedHosts();
            }

            /**
             * Gets the list of trusted proxies.
             *
             * @return array An array of trusted proxies
             * @static
             */
            public static function getTrustedProxies()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::getTrustedProxies();
            }

            /**
             * Generates a normalized URI (URL) for the Request.
             *
             * @return string A normalized URI (URL) for the Request
             * @see getQueryString()
             * @static
             */
            public static function getUri()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getUri();
            }

            /**
             * Generates a normalized URI for the given path.
             *
             * @param  string $path A path to use instead of the current one
             * @return string The normalized URI for the path
             * @static
             */
            public static function getUriForPath($path)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getUriForPath($path);
            }

            /**
             * Returns the user.
             *
             * @return string|null
             * @static
             */
            public static function getUser()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getUser();
            }

            /**
             * Gets the user info.
             *
             * @return string A user name and, optionally, scheme-specific information about how to gain authorization to access the server
             * @static
             */
            public static function getUserInfo()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->getUserInfo();
            }

            /**
             * Get the user resolver callback.
             *
             * @return \Closure
             * @static
             */
            public static function getUserResolver()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->getUserResolver();
            }

            /**
             * Determine if the request contains a given input item key.
             *
             * @param  array|string $key
             * @return bool
             * @static
             */
            public static function has($key)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->has($key);
            }

            /**
             * Determine if the request contains any of the given inputs.
             *
             * @param  array|string $keys
             * @return bool
             * @static
             */
            public static function hasAny($keys)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->hasAny($keys);
            }

            /**
             * Determine if a cookie is set on the request.
             *
             * @param  string $key
             * @return bool
             * @static
             */
            public static function hasCookie($key)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->hasCookie($key);
            }

            /**
             * Determine if the uploaded data contains a file.
             *
             * @param  string $key
             * @return bool
             * @static
             */
            public static function hasFile($key)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->hasFile($key);
            }

            /**
             * Determine if a header is set on the request.
             *
             * @param  string $key
             * @return bool
             * @static
             */
            public static function hasHeader($key)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->hasHeader($key);
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Http\Request::hasMacro($name);
            }

            /**
             * Whether the request contains a Session which was started in one of the
             * previous requests.
             *
             * @return bool
             * @static
             */
            public static function hasPreviousSession()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->hasPreviousSession();
            }

            /**
             * Whether the request contains a Session object.
             *
             * This method does not give any information about the state of the session object,
             * like whether the session is started or not. It is just a way to check if this Request
             * is associated with a Session instance.
             *
             * @return bool true when the Request contains a Session object, false otherwise
             * @static
             */
            public static function hasSession()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->hasSession();
            }

            /**
             * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
             * @static
             */
            public static function hasValidRelativeSignature()
            {
                return \Illuminate\Http\Request::hasValidRelativeSignature();
            }

            /**
             * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
             * @param mixed $absolute
             * @static
             */
            public static function hasValidSignature($absolute = true)
            {
                return \Illuminate\Http\Request::hasValidSignature($absolute);
            }

            /**
             * Retrieve a header from the request.
             *
             * @param  string|null       $key
             * @param  array|string|null $default
             * @return array|string|null
             * @static
             */
            public static function header($key = null, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->header($key, $default);
            }

            /**
             * Sets the parameters for this request.
             *
             * This method also re-initializes all properties.
             *
             * @param array                $query      The GET parameters
             * @param array                $request    The POST parameters
             * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
             * @param array                $cookies    The COOKIE parameters
             * @param array                $files      The FILES parameters
             * @param array                $server     The SERVER parameters
             * @param resource|string|null $content    The raw body data
             * @static
             */
            public static function initialize($query = [], $request = [], $attributes = [], $cookies = [], $files = [], $server = [], $content = null)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->initialize($query, $request, $attributes, $cookies, $files, $server, $content);
            }

            /**
             * Retrieve an input item from the request.
             *
             * @param  string|null $key
             * @param  mixed       $default
             * @return mixed
             * @static
             */
            public static function input($key = null, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->input($key, $default);
            }

            /**
             * Return the Request instance.
             *
             * @return \Illuminate\Http\Request
             * @static
             */
            public static function instance()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->instance();
            }

            /**
             * Get the client IP address.
             *
             * @return string|null
             * @static
             */
            public static function ip()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->ip();
            }

            /**
             * Get the client IP addresses.
             *
             * @return array
             * @static
             */
            public static function ips()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->ips();
            }

            /**
             * Determine if the current request URI matches a pattern.
             *
             * @param  mixed $patterns
             * @return bool
             * @static
             */
            public static function is(...$patterns)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->is(...$patterns);
            }

            /**
             * Indicates whether this request originated from a trusted proxy.
             *
             * This can be useful to determine whether or not to trust the
             * contents of a proxy-specific header.
             *
             * @return bool true if the request came from a trusted proxy, false otherwise
             * @static
             */
            public static function isFromTrustedProxy()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->isFromTrustedProxy();
            }

            /**
             * Determine if the request is sending JSON.
             *
             * @return bool
             * @static
             */
            public static function isJson()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->isJson();
            }

            /**
             * Checks if the request method is of specified type.
             *
             * @param  string $method Uppercase request method (GET, POST etc)
             * @return bool
             * @static
             */
            public static function isMethod($method)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->isMethod($method);
            }

            /**
             * Checks whether the method is cacheable or not.
             *
             * @see https://tools.ietf.org/html/rfc7231#section-4.2.3
             * @return bool True for GET and HEAD, false otherwise
             * @static
             */
            public static function isMethodCacheable()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->isMethodCacheable();
            }

            /**
             * Checks whether or not the method is idempotent.
             *
             * @return bool
             * @static
             */
            public static function isMethodIdempotent()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->isMethodIdempotent();
            }

            /**
             * Checks whether or not the method is safe.
             *
             * @see https://tools.ietf.org/html/rfc7231#section-4.2.1
             * @return bool
             * @static
             */
            public static function isMethodSafe()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->isMethodSafe();
            }

            /**
             * @return bool
             * @static
             */
            public static function isNoCache()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->isNoCache();
            }

            /**
             * Determine if the request contains an empty value for an input item.
             *
             * @param  array|string $key
             * @return bool
             * @static
             */
            public static function isNotFilled($key)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->isNotFilled($key);
            }

            /**
             * Checks whether the request is secure or not.
             *
             * This method can read the client protocol from the "X-Forwarded-Proto" header
             * when trusted proxies were set via "setTrustedProxies()".
             *
             * The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
             *
             * @return bool
             * @static
             */
            public static function isSecure()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->isSecure();
            }

            /**
             * Returns true if the request is an XMLHttpRequest.
             *
             * It works if your JavaScript library sets an X-Requested-With HTTP header.
             * It is known to work with common JavaScript frameworks:
             *
             * @see https://wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
             * @return bool true if the request is an XMLHttpRequest, false otherwise
             * @static
             */
            public static function isXmlHttpRequest()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->isXmlHttpRequest();
            }

            /**
             * Get the JSON payload for the request.
             *
             * @param  string|null                                          $key
             * @param  mixed                                                $default
             * @return mixed|\Symfony\Component\HttpFoundation\ParameterBag
             * @static
             */
            public static function json($key = null, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->json($key, $default);
            }

            /**
             * Get the keys for all of the input and files.
             *
             * @return array
             * @static
             */
            public static function keys()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->keys();
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Http\Request::macro($name, $macro);
            }

            /**
             * Determine if the given content types match.
             *
             * @param  string $actual
             * @param  string $type
             * @return bool
             * @static
             */
            public static function matchesType($actual, $type)
            {
                return \Illuminate\Http\Request::matchesType($actual, $type);
            }

            /**
             * Merge new input into the current request's input array.
             *
             * @param  array                    $input
             * @return \Illuminate\Http\Request
             * @static
             */
            public static function merge($input)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->merge($input);
            }

            /**
             * Get the request method.
             *
             * @return string
             * @static
             */
            public static function method()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->method();
            }

            /**
             * Determine if the request is missing a given input item key.
             *
             * @param  array|string $key
             * @return bool
             * @static
             */
            public static function missing($key)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->missing($key);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Http\Request::mixin($mixin, $replace);
            }

            /**
             * Normalizes a query string.
             *
             * It builds a normalized query string, where keys/value pairs are alphabetized,
             * have consistent escaping and unneeded delimiters are removed.
             *
             * @static
             * @param  mixed  $qs
             * @return string A normalized query string for the Request
             */
            public static function normalizeQueryString($qs)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::normalizeQueryString($qs);
            }

            /**
             * Determine if the given offset exists.
             *
             * @param  string $offset
             * @return bool
             * @static
             */
            public static function offsetExists($offset)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->offsetExists($offset);
            }

            /**
             * Get the value at the given offset.
             *
             * @param  string $offset
             * @return mixed
             * @static
             */
            public static function offsetGet($offset)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->offsetGet($offset);
            }

            /**
             * Set the value at the given offset.
             *
             * @param  string $offset
             * @param  mixed  $value
             * @return void
             * @static
             */
            public static function offsetSet($offset, $value)
            {
                // @var \Illuminate\Http\Request $instance
                $instance->offsetSet($offset, $value);
            }

            /**
             * Remove the value at the given offset.
             *
             * @param  string $offset
             * @return void
             * @static
             */
            public static function offsetUnset($offset)
            {
                // @var \Illuminate\Http\Request $instance
                $instance->offsetUnset($offset);
            }

            /**
             * Retrieve an old input item.
             *
             * @param  string|null       $key
             * @param  array|string|null $default
             * @return array|string
             * @static
             */
            public static function old($key = null, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->old($key, $default);
            }

            /**
             * Get a subset containing the provided keys with values from the input data.
             *
             * @param  array|mixed $keys
             * @return array
             * @static
             */
            public static function only($keys)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->only($keys);
            }

            /**
             * Overrides the PHP global variables according to this request instance.
             *
             * It overrides $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE.
             * $_FILES is never overridden, see rfc1867
             *
             * @static
             */
            public static function overrideGlobals()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->overrideGlobals();
            }

            /**
             * Get the current path info for the request.
             *
             * @return string
             * @static
             */
            public static function path()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->path();
            }

            /**
             * Determine if the request is the result of a PJAX call.
             *
             * @return bool
             * @static
             */
            public static function pjax()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->pjax();
            }

            /**
             * Retrieve a request payload item from the request.
             *
             * @param  string|null       $key
             * @param  array|string|null $default
             * @return array|string|null
             * @static
             */
            public static function post($key = null, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->post($key, $default);
            }

            /**
             * Return the most suitable content type from the given array based on content negotiation.
             *
             * @param  array|string $contentTypes
             * @return string|null
             * @static
             */
            public static function prefers($contentTypes)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->prefers($contentTypes);
            }

            /**
             * Checks whether the client browser prefers safe content or not according to RFC8674.
             *
             * @see https://tools.ietf.org/html/rfc8674
             * @static
             */
            public static function preferSafeContent()
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->preferSafeContent();
            }

            /**
             * Determine if the request is the result of a prefetch call.
             *
             * @return bool
             * @static
             */
            public static function prefetch()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->prefetch();
            }

            /**
             * Retrieve a query string item from the request.
             *
             * @param  string|null       $key
             * @param  array|string|null $default
             * @return array|string|null
             * @static
             */
            public static function query($key = null, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->query($key, $default);
            }

            /**
             * Replace the input for the current request.
             *
             * @param  array                    $input
             * @return \Illuminate\Http\Request
             * @static
             */
            public static function replace($input)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->replace($input);
            }

            /**
             * Get the root URL for the application.
             *
             * @return string
             * @static
             */
            public static function root()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->root();
            }

            /**
             * Get the route handling the request.
             *
             * @param  string|null                                  $param
             * @param  mixed                                        $default
             * @return \Illuminate\Routing\Route|object|string|null
             * @static
             */
            public static function route($param = null, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->route($param, $default);
            }

            /**
             * Determine if the route name matches a given pattern.
             *
             * @param  mixed $patterns
             * @return bool
             * @static
             */
            public static function routeIs(...$patterns)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->routeIs(...$patterns);
            }

            /**
             * Determine if the request is over HTTPS.
             *
             * @return bool
             * @static
             */
            public static function secure()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->secure();
            }

            /**
             * Get a segment from the URI (1 based index).
             *
             * @param  int         $index
             * @param  string|null $default
             * @return string|null
             * @static
             */
            public static function segment($index, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->segment($index, $default);
            }

            /**
             * Get all of the segments for the request path.
             *
             * @return array
             * @static
             */
            public static function segments()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->segments();
            }

            /**
             * Retrieve a server variable from the request.
             *
             * @param  string|null       $key
             * @param  array|string|null $default
             * @return array|string|null
             * @static
             */
            public static function server($key = null, $default = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->server($key, $default);
            }

            /**
             * Get the session associated with the request.
             *
             * @throws \RuntimeException
             * @return \Illuminate\Session\Store
             * @static
             */
            public static function session()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->session();
            }

            /**
             * Sets the default locale.
             *
             * @static
             * @param mixed $locale
             */
            public static function setDefaultLocale($locale)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->setDefaultLocale($locale);
            }

            /**
             * Sets a callable able to create a Request instance.
             *
             * This is mainly useful when you need to override the Request class
             * to keep BC with an existing system. It should not be used for any
             * other purpose.
             *
             * @static
             * @param mixed $callable
             */
            public static function setFactory($callable)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::setFactory($callable);
            }

            /**
             * Associates a format with mime types.
             *
             * @param array|string $mimeTypes The associated mime types (the preferred one must be the first as it will be used as the content type)
             * @param mixed        $format
             * @static
             */
            public static function setFormat($format, $mimeTypes)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->setFormat($format, $mimeTypes);
            }

            /**
             * Set the JSON payload for the request.
             *
             * @param  \Symfony\Component\HttpFoundation\ParameterBag $json
             * @return \Illuminate\Http\Request
             * @static
             */
            public static function setJson($json)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->setJson($json);
            }

            /**
             * Set the session instance on the request.
             *
             * @param  \Illuminate\Contracts\Session\Session $session
             * @return void
             * @static
             */
            public static function setLaravelSession($session)
            {
                // @var \Illuminate\Http\Request $instance
                $instance->setLaravelSession($session);
            }

            /**
             * Sets the locale.
             *
             * @static
             * @param mixed $locale
             */
            public static function setLocale($locale)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->setLocale($locale);
            }

            /**
             * Sets the request method.
             *
             * @static
             * @param mixed $method
             */
            public static function setMethod($method)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->setMethod($method);
            }

            /**
             * Sets the request format.
             *
             * @static
             * @param mixed $format
             */
            public static function setRequestFormat($format)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->setRequestFormat($format);
            }

            /**
             * Set the route resolver callback.
             *
             * @param  \Closure                 $callback
             * @return \Illuminate\Http\Request
             * @static
             */
            public static function setRouteResolver($callback)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->setRouteResolver($callback);
            }

            /**
             * @static
             * @param mixed $session
             */
            public static function setSession($session)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->setSession($session);
            }

            /**
             * @internal
             * @static
             * @param mixed $factory
             */
            public static function setSessionFactory($factory)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                // @var \Illuminate\Http\Request $instance
                return $instance->setSessionFactory($factory);
            }

            /**
             * Sets a list of trusted host patterns.
             *
             * You should only list the hosts you manage using regexs.
             *
             * @param array $hostPatterns A list of trusted host patterns
             * @static
             */
            public static function setTrustedHosts($hostPatterns)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::setTrustedHosts($hostPatterns);
            }

            /**
             * Sets a list of trusted proxies.
             *
             * You should only list the reverse proxies that you manage directly.
             *
             * @param array $proxies          A list of trusted proxies, the string 'REMOTE_ADDR' will be replaced with $_SERVER['REMOTE_ADDR']
             * @param int   $trustedHeaderSet A bit field of Request::HEADER_*, to set which headers to trust from your proxies
             * @static
             */
            public static function setTrustedProxies($proxies, $trustedHeaderSet)
            {            //Method inherited from \Symfony\Component\HttpFoundation\Request
                return \Illuminate\Http\Request::setTrustedProxies($proxies, $trustedHeaderSet);
            }

            /**
             * Set the user resolver callback.
             *
             * @param  \Closure                 $callback
             * @return \Illuminate\Http\Request
             * @static
             */
            public static function setUserResolver($callback)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->setUserResolver($callback);
            }

            /**
             * Get all of the input and files for the request.
             *
             * @return array
             * @static
             */
            public static function toArray()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->toArray();
            }

            /**
             * Get the URL (no query string) for the request.
             *
             * @return string
             * @static
             */
            public static function url()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->url();
            }

            /**
             * Get the user making the request.
             *
             * @param  string|null $guard
             * @return mixed
             * @static
             */
            public static function user($guard = null)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->user($guard);
            }

            /**
             * Get the client user agent.
             *
             * @return string|null
             * @static
             */
            public static function userAgent()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->userAgent();
            }

            /**
             * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
             * @param array $rules
             * @param mixed $params
             * @static
             */
            public static function validate($rules, ...$params)
            {
                return \Illuminate\Http\Request::validate($rules, ...$params);
            }

            /**
             * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
             * @param string $errorBag
             * @param array  $rules
             * @param mixed  $params
             * @static
             */
            public static function validateWithBag($errorBag, $rules, ...$params)
            {
                return \Illuminate\Http\Request::validateWithBag($errorBag, $rules, ...$params);
            }

            /**
             * Determine if the current request is asking for JSON.
             *
             * @return bool
             * @static
             */
            public static function wantsJson()
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->wantsJson();
            }

            /**
             * Apply the callback if the request contains a non-empty value for the given input item key.
             *
             * @param  string      $key
             * @param  callable    $callback
             * @return $this|mixed
             * @static
             */
            public static function whenFilled($key, $callback)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->whenFilled($key, $callback);
            }

            /**
             * Apply the callback if the request contains the given input item key.
             *
             * @param  string      $key
             * @param  callable    $callback
             * @return $this|mixed
             * @static
             */
            public static function whenHas($key, $callback)
            {
                // @var \Illuminate\Http\Request $instance
                return $instance->whenHas($key, $callback);
            }
        }
        /**
         * @see \Illuminate\Contracts\Routing\ResponseFactory
         */
        class Response
        {
            /**
             * Create a new file download response.
             *
             * @param  \SplFileInfo|string                                  $file
             * @param  string|null                                          $name
             * @param  array                                                $headers
             * @param  string|null                                          $disposition
             * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
             * @static
             */
            public static function download($file, $name = null, $headers = [], $disposition = 'attachment')
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->download($file, $name, $headers, $disposition);
            }

            /**
             * Return the raw contents of a binary file.
             *
             * @param  \SplFileInfo|string                                  $file
             * @param  array                                                $headers
             * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
             * @static
             */
            public static function file($file, $headers = [])
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->file($file, $headers);
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Routing\ResponseFactory::hasMacro($name);
            }

            /**
             * Create a new JSON response instance.
             *
             * @param  mixed                         $data
             * @param  int                           $status
             * @param  array                         $headers
             * @param  int                           $options
             * @return \Illuminate\Http\JsonResponse
             * @static
             */
            public static function json($data = [], $status = 200, $headers = [], $options = 0)
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->json($data, $status, $headers, $options);
            }

            /**
             * Create a new JSONP response instance.
             *
             * @param  string                        $callback
             * @param  mixed                         $data
             * @param  int                           $status
             * @param  array                         $headers
             * @param  int                           $options
             * @return \Illuminate\Http\JsonResponse
             * @static
             */
            public static function jsonp($callback, $data = [], $status = 200, $headers = [], $options = 0)
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->jsonp($callback, $data, $status, $headers, $options);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Routing\ResponseFactory::macro($name, $macro);
            }

            /**
             * Create a new response instance.
             *
             * @param  string                    $content
             * @param  int                       $status
             * @param  array                     $headers
             * @return \Illuminate\Http\Response
             * @static
             */
            public static function make($content = '', $status = 200, $headers = [])
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->make($content, $status, $headers);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Routing\ResponseFactory::mixin($mixin, $replace);
            }

            /**
             * Create a new "no content" response.
             *
             * @param  int                       $status
             * @param  array                     $headers
             * @return \Illuminate\Http\Response
             * @static
             */
            public static function noContent($status = 204, $headers = [])
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->noContent($status, $headers);
            }

            /**
             * Create a new redirect response, while putting the current URL in the session.
             *
             * @param  string                            $path
             * @param  int                               $status
             * @param  array                             $headers
             * @param  bool|null                         $secure
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function redirectGuest($path, $status = 302, $headers = [], $secure = null)
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->redirectGuest($path, $status, $headers, $secure);
            }

            /**
             * Create a new redirect response to the given path.
             *
             * @param  string                            $path
             * @param  int                               $status
             * @param  array                             $headers
             * @param  bool|null                         $secure
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function redirectTo($path, $status = 302, $headers = [], $secure = null)
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->redirectTo($path, $status, $headers, $secure);
            }

            /**
             * Create a new redirect response to a controller action.
             *
             * @param  string                            $action
             * @param  mixed                             $parameters
             * @param  int                               $status
             * @param  array                             $headers
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function redirectToAction($action, $parameters = [], $status = 302, $headers = [])
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->redirectToAction($action, $parameters, $status, $headers);
            }

            /**
             * Create a new redirect response to the previously intended location.
             *
             * @param  string                            $default
             * @param  int                               $status
             * @param  array                             $headers
             * @param  bool|null                         $secure
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function redirectToIntended($default = '/', $status = 302, $headers = [], $secure = null)
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->redirectToIntended($default, $status, $headers, $secure);
            }

            /**
             * Create a new redirect response to a named route.
             *
             * @param  string                            $route
             * @param  mixed                             $parameters
             * @param  int                               $status
             * @param  array                             $headers
             * @return \Illuminate\Http\RedirectResponse
             * @static
             */
            public static function redirectToRoute($route, $parameters = [], $status = 302, $headers = [])
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->redirectToRoute($route, $parameters, $status, $headers);
            }

            /**
             * Create a new streamed response instance.
             *
             * @param  \Closure                                           $callback
             * @param  int                                                $status
             * @param  array                                              $headers
             * @return \Symfony\Component\HttpFoundation\StreamedResponse
             * @static
             */
            public static function stream($callback, $status = 200, $headers = [])
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->stream($callback, $status, $headers);
            }

            /**
             * Create a new streamed response instance as a file download.
             *
             * @param  \Closure                                           $callback
             * @param  string|null                                        $name
             * @param  array                                              $headers
             * @param  string|null                                        $disposition
             * @return \Symfony\Component\HttpFoundation\StreamedResponse
             * @static
             */
            public static function streamDownload($callback, $name = null, $headers = [], $disposition = 'attachment')
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->streamDownload($callback, $name, $headers, $disposition);
            }

            /**
             * Create a new response for a given view.
             *
             * @param  array|string              $view
             * @param  array                     $data
             * @param  int                       $status
             * @param  array                     $headers
             * @return \Illuminate\Http\Response
             * @static
             */
            public static function view($view, $data = [], $status = 200, $headers = [])
            {
                // @var \Illuminate\Routing\ResponseFactory $instance
                return $instance->view($view, $data, $status, $headers);
            }
        }
        /**
         * @method static \Illuminate\Routing\RouteRegistrar as(string $value)
         * @method static \Illuminate\Routing\RouteRegistrar domain(string $value)
         * @method static \Illuminate\Routing\RouteRegistrar middleware(array|string|null $middleware)
         * @method static \Illuminate\Routing\RouteRegistrar name(string $value)
         * @method static \Illuminate\Routing\RouteRegistrar namespace(string|null $value)
         * @method static \Illuminate\Routing\RouteRegistrar prefix(string  $prefix)
         * @method static \Illuminate\Routing\RouteRegistrar where(array  $where)
         * @see \Illuminate\Routing\Router
         */
        class Route
        {
            /**
             * Add a route to the underlying route collection.
             *
             * @param  array|string               $methods
             * @param  string                     $uri
             * @param  array|callable|string|null $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function addRoute($methods, $uri, $action)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->addRoute($methods, $uri, $action);
            }

            /**
             * Register a short-hand name for a middleware.
             *
             * @param  string                     $name
             * @param  string                     $class
             * @return \Illuminate\Routing\Router
             * @static
             */
            public static function aliasMiddleware($name, $class)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->aliasMiddleware($name, $class);
            }

            /**
             * Register a new route responding to all verbs.
             *
             * @param  string                     $uri
             * @param  array|callable|string|null $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function any($uri, $action = null)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->any($uri, $action);
            }

            /**
             * Route an API resource to a controller.
             *
             * @param  string                                          $name
             * @param  string                                          $controller
             * @param  array                                           $options
             * @return \Illuminate\Routing\PendingResourceRegistration
             * @static
             */
            public static function apiResource($name, $controller, $options = [])
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->apiResource($name, $controller, $options);
            }

            /**
             * Register an array of API resource controllers.
             *
             * @param  array $resources
             * @param  array $options
             * @return void
             * @static
             */
            public static function apiResources($resources, $options = [])
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->apiResources($resources, $options);
            }

            /**
             * @see \Laravel\Ui\AuthRouteMethods::auth()
             * @param mixed $options
             * @static
             */
            public static function auth($options = [])
            {
                return \Illuminate\Routing\Router::auth($options);
            }

            /**
             * Add a new route parameter binder.
             *
             * @param  string          $key
             * @param  callable|string $binder
             * @return void
             * @static
             */
            public static function bind($key, $binder)
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->bind($key, $binder);
            }

            /**
             * @see \Laravel\Ui\AuthRouteMethods::confirmPassword()
             * @static
             */
            public static function confirmPassword()
            {
                return \Illuminate\Routing\Router::confirmPassword();
            }

            /**
             * Get the currently dispatched route instance.
             *
             * @return \Illuminate\Routing\Route|null
             * @static
             */
            public static function current()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->current();
            }

            /**
             * Get the current route action.
             *
             * @return string|null
             * @static
             */
            public static function currentRouteAction()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->currentRouteAction();
            }

            /**
             * Get the current route name.
             *
             * @return string|null
             * @static
             */
            public static function currentRouteName()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->currentRouteName();
            }

            /**
             * Determine if the current route matches a pattern.
             *
             * @param  mixed $patterns
             * @return bool
             * @static
             */
            public static function currentRouteNamed(...$patterns)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->currentRouteNamed(...$patterns);
            }

            /**
             * Determine if the current route action matches a given action.
             *
             * @param  string $action
             * @return bool
             * @static
             */
            public static function currentRouteUses($action)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->currentRouteUses($action);
            }

            /**
             * Register a new DELETE route with the router.
             *
             * @param  string                     $uri
             * @param  array|callable|string|null $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function delete($uri, $action = null)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->delete($uri, $action);
            }

            /**
             * Dispatch the request to the application.
             *
             * @param  \Illuminate\Http\Request                   $request
             * @return \Symfony\Component\HttpFoundation\Response
             * @static
             */
            public static function dispatch($request)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->dispatch($request);
            }

            /**
             * Dispatch the request to a route and return the response.
             *
             * @param  \Illuminate\Http\Request                   $request
             * @return \Symfony\Component\HttpFoundation\Response
             * @static
             */
            public static function dispatchToRoute($request)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->dispatchToRoute($request);
            }

            /**
             * @see \Laravel\Ui\AuthRouteMethods::emailVerification()
             * @static
             */
            public static function emailVerification()
            {
                return \Illuminate\Routing\Router::emailVerification();
            }

            /**
             * Register a new Fallback route with the router.
             *
             * @param  array|callable|string|null $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function fallback($action)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->fallback($action);
            }

            /**
             * Flush the router's middleware groups.
             *
             * @return \Illuminate\Routing\Router
             * @static
             */
            public static function flushMiddlewareGroups()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->flushMiddlewareGroups();
            }

            /**
             * Gather the middleware for the given route with resolved class names.
             *
             * @param  \Illuminate\Routing\Route $route
             * @return array
             * @static
             */
            public static function gatherRouteMiddleware($route)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->gatherRouteMiddleware($route);
            }

            /**
             * Register a new GET route with the router.
             *
             * @param  string                     $uri
             * @param  array|callable|string|null $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function get($uri, $action = null)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->get($uri, $action);
            }

            /**
             * Get the binding callback for a given binding.
             *
             * @param  string        $key
             * @return \Closure|null
             * @static
             */
            public static function getBindingCallback($key)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->getBindingCallback($key);
            }

            /**
             * Get the request currently being dispatched.
             *
             * @return \Illuminate\Http\Request
             * @static
             */
            public static function getCurrentRequest()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->getCurrentRequest();
            }

            /**
             * Get the currently dispatched route instance.
             *
             * @return \Illuminate\Routing\Route|null
             * @static
             */
            public static function getCurrentRoute()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->getCurrentRoute();
            }

            /**
             * Get the current group stack for the router.
             *
             * @return array
             * @static
             */
            public static function getGroupStack()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->getGroupStack();
            }

            /**
             * Get the prefix from the last group on the stack.
             *
             * @return string
             * @static
             */
            public static function getLastGroupPrefix()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->getLastGroupPrefix();
            }

            /**
             * Get all of the defined middleware short-hand names.
             *
             * @return array
             * @static
             */
            public static function getMiddleware()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->getMiddleware();
            }

            /**
             * Get all of the defined middleware groups.
             *
             * @return array
             * @static
             */
            public static function getMiddlewareGroups()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->getMiddlewareGroups();
            }

            /**
             * Get the global "where" patterns.
             *
             * @return array
             * @static
             */
            public static function getPatterns()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->getPatterns();
            }

            /**
             * Get the underlying route collection.
             *
             * @return \Illuminate\Routing\RouteCollectionInterface
             * @static
             */
            public static function getRoutes()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->getRoutes();
            }

            /**
             * Create a route group with shared attributes.
             *
             * @param  array           $attributes
             * @param  \Closure|string $routes
             * @return void
             * @static
             */
            public static function group($attributes, $routes)
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->group($attributes, $routes);
            }

            /**
             * Check if a route with the given name exists.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function has($name)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->has($name);
            }

            /**
             * Determine if the router currently has a group stack.
             *
             * @return bool
             * @static
             */
            public static function hasGroupStack()
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->hasGroupStack();
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Routing\Router::hasMacro($name);
            }

            /**
             * Check if a middlewareGroup with the given name exists.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMiddlewareGroup($name)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->hasMiddlewareGroup($name);
            }

            /**
             * @see \Lab404\Impersonate\ImpersonateServiceProvider::registerRoutesMacro()
             * @static
             */
            public static function impersonate()
            {
                return \Illuminate\Routing\Router::impersonate();
            }

            /**
             * Get a route parameter for the current route.
             *
             * @param  string      $key
             * @param  string|null $default
             * @return mixed
             * @static
             */
            public static function input($key, $default = null)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->input($key, $default);
            }

            /**
             * Alias for the "currentRouteNamed" method.
             *
             * @param  mixed $patterns
             * @return bool
             * @static
             */
            public static function is(...$patterns)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->is(...$patterns);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Routing\Router::macro($name, $macro);
            }

            /**
             * Dynamically handle calls to the class.
             *
             * @param  string                  $method
             * @param  array                   $parameters
             * @throws \BadMethodCallException
             * @return mixed
             * @static
             */
            public static function macroCall($method, $parameters)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->macroCall($method, $parameters);
            }

            /**
             * Register a new route with the given verbs.
             *
             * @param  array|string               $methods
             * @param  string                     $uri
             * @param  array|callable|string|null $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function match($methods, $uri, $action = null)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->match($methods, $uri, $action);
            }

            /**
             * Register a route matched event listener.
             *
             * @param  callable|string $callback
             * @return void
             * @static
             */
            public static function matched($callback)
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->matched($callback);
            }

            /**
             * Merge the given array with the last group stack.
             *
             * @param  array $new
             * @param  bool  $prependExistingPrefix
             * @return array
             * @static
             */
            public static function mergeWithLastGroup($new, $prependExistingPrefix = true)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->mergeWithLastGroup($new, $prependExistingPrefix);
            }

            /**
             * Register a group of middleware.
             *
             * @param  string                     $name
             * @param  array                      $middleware
             * @return \Illuminate\Routing\Router
             * @static
             */
            public static function middlewareGroup($name, $middleware)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->middlewareGroup($name, $middleware);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Routing\Router::mixin($mixin, $replace);
            }

            /**
             * Register a model binder for a wildcard.
             *
             * @param  string        $key
             * @param  string        $class
             * @param  \Closure|null $callback
             * @return void
             * @static
             */
            public static function model($key, $class, $callback = null)
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->model($key, $class, $callback);
            }

            /**
             * Create a new Route object.
             *
             * @param  array|string              $methods
             * @param  string                    $uri
             * @param  mixed                     $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function newRoute($methods, $uri, $action)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->newRoute($methods, $uri, $action);
            }

            /**
             * Register a new OPTIONS route with the router.
             *
             * @param  string                     $uri
             * @param  array|callable|string|null $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function options($uri, $action = null)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->options($uri, $action);
            }

            /**
             * Register a new PATCH route with the router.
             *
             * @param  string                     $uri
             * @param  array|callable|string|null $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function patch($uri, $action = null)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->patch($uri, $action);
            }

            /**
             * Set a global where pattern on all routes.
             *
             * @param  string $key
             * @param  string $pattern
             * @return void
             * @static
             */
            public static function pattern($key, $pattern)
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->pattern($key, $pattern);
            }

            /**
             * Set a group of global where patterns on all routes.
             *
             * @param  array $patterns
             * @return void
             * @static
             */
            public static function patterns($patterns)
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->patterns($patterns);
            }

            /**
             * Create a permanent redirect from one URI to another.
             *
             * @param  string                    $uri
             * @param  string                    $destination
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function permanentRedirect($uri, $destination)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->permanentRedirect($uri, $destination);
            }

            /**
             * Register a new POST route with the router.
             *
             * @param  string                     $uri
             * @param  array|callable|string|null $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function post($uri, $action = null)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->post($uri, $action);
            }

            /**
             * Create a response instance from the given value.
             *
             * @param  \Symfony\Component\HttpFoundation\Request  $request
             * @param  mixed                                      $response
             * @return \Symfony\Component\HttpFoundation\Response
             * @static
             */
            public static function prepareResponse($request, $response)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->prepareResponse($request, $response);
            }

            /**
             * Add a middleware to the beginning of a middleware group.
             *
             * If the middleware is already in the group, it will not be added again.
             *
             * @param  string                     $group
             * @param  string                     $middleware
             * @return \Illuminate\Routing\Router
             * @static
             */
            public static function prependMiddlewareToGroup($group, $middleware)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->prependMiddlewareToGroup($group, $middleware);
            }

            /**
             * Add a middleware to the end of a middleware group.
             *
             * If the middleware is already in the group, it will not be added again.
             *
             * @param  string                     $group
             * @param  string                     $middleware
             * @return \Illuminate\Routing\Router
             * @static
             */
            public static function pushMiddlewareToGroup($group, $middleware)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->pushMiddlewareToGroup($group, $middleware);
            }

            /**
             * Register a new PUT route with the router.
             *
             * @param  string                     $uri
             * @param  array|callable|string|null $action
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function put($uri, $action = null)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->put($uri, $action);
            }

            /**
             * Create a redirect from one URI to another.
             *
             * @param  string                    $uri
             * @param  string                    $destination
             * @param  int                       $status
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function redirect($uri, $destination, $status = 302)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->redirect($uri, $destination, $status);
            }

            /**
             * @see \Laravel\Ui\AuthRouteMethods::resetPassword()
             * @static
             */
            public static function resetPassword()
            {
                return \Illuminate\Routing\Router::resetPassword();
            }

            /**
             * Route a resource to a controller.
             *
             * @param  string                                          $name
             * @param  string                                          $controller
             * @param  array                                           $options
             * @return \Illuminate\Routing\PendingResourceRegistration
             * @static
             */
            public static function resource($name, $controller, $options = [])
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->resource($name, $controller, $options);
            }

            /**
             * Set the global resource parameter mapping.
             *
             * @param  array $parameters
             * @return void
             * @static
             */
            public static function resourceParameters($parameters = [])
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->resourceParameters($parameters);
            }

            /**
             * Register an array of resource controllers.
             *
             * @param  array $resources
             * @param  array $options
             * @return void
             * @static
             */
            public static function resources($resources, $options = [])
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->resources($resources, $options);
            }

            /**
             * Get or set the verbs used in the resource URIs.
             *
             * @param  array      $verbs
             * @return array|null
             * @static
             */
            public static function resourceVerbs($verbs = [])
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->resourceVerbs($verbs);
            }

            /**
             * Return the response returned by the given route.
             *
             * @param  string                                     $name
             * @return \Symfony\Component\HttpFoundation\Response
             * @static
             */
            public static function respondWithRoute($name)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->respondWithRoute($name);
            }

            /**
             * Set the compiled route collection instance.
             *
             * @param  array $routes
             * @return void
             * @static
             */
            public static function setCompiledRoutes($routes)
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->setCompiledRoutes($routes);
            }

            /**
             * Set the route collection instance.
             *
             * @param  \Illuminate\Routing\RouteCollection $routes
             * @return void
             * @static
             */
            public static function setRoutes($routes)
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->setRoutes($routes);
            }

            /**
             * Set the unmapped global resource parameters to singular.
             *
             * @param  bool $singular
             * @return void
             * @static
             */
            public static function singularResourceParameters($singular = true)
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->singularResourceParameters($singular);
            }

            /**
             * Substitute the route bindings onto the route.
             *
             * @param  \Illuminate\Routing\Route                            $route
             * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function substituteBindings($route)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->substituteBindings($route);
            }

            /**
             * Substitute the implicit Eloquent model bindings for the route.
             *
             * @param  \Illuminate\Routing\Route                            $route
             * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
             * @return void
             * @static
             */
            public static function substituteImplicitBindings($route)
            {
                // @var \Illuminate\Routing\Router $instance
                $instance->substituteImplicitBindings($route);
            }

            /**
             * Static version of prepareResponse.
             *
             * @param  \Symfony\Component\HttpFoundation\Request  $request
             * @param  mixed                                      $response
             * @return \Symfony\Component\HttpFoundation\Response
             * @static
             */
            public static function toResponse($request, $response)
            {
                return \Illuminate\Routing\Router::toResponse($request, $response);
            }

            /**
             * Remove any duplicate middleware from the given array.
             *
             * @param  array $middleware
             * @return array
             * @static
             */
            public static function uniqueMiddleware($middleware)
            {
                return \Illuminate\Routing\Router::uniqueMiddleware($middleware);
            }

            /**
             * Alias for the "currentRouteUses" method.
             *
             * @param  array $patterns
             * @return bool
             * @static
             */
            public static function uses(...$patterns)
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->uses(...$patterns);
            }

            /**
             * Register a new route that returns a view.
             *
             * @param  string                    $uri
             * @param  string                    $view
             * @param  array                     $data
             * @param  array|int                 $status
             * @param  array                     $headers
             * @return \Illuminate\Routing\Route
             * @static
             */
            public static function view($uri, $view, $data = [], $status = 200, $headers = [])
            {
                // @var \Illuminate\Routing\Router $instance
                return $instance->view($uri, $view, $data, $status, $headers);
            }
        }
        /**
         * @see \Illuminate\Database\Schema\Builder
         */
        class Schema
        {
            /**
             * Set the Schema Blueprint resolver callback.
             *
             * @param  \Closure $resolver
             * @return void
             * @static
             */
            public static function blueprintResolver($resolver)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->blueprintResolver($resolver);
            }

            /**
             * Create a new table on the schema.
             *
             * @param  string   $table
             * @param  \Closure $callback
             * @return void
             * @static
             */
            public static function create($table, $callback)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->create($table, $callback);
            }

            /**
             * Create a database in the schema.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function createDatabase($name)
            {
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->createDatabase($name);
            }

            /**
             * Set the default morph key type for migrations.
             *
             * @param  string $type
             * @return void
             * @static
             */
            public static function defaultMorphKeyType($type)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                \Illuminate\Database\Schema\MySqlBuilder::defaultMorphKeyType($type);
            }

            /**
             * Set the default string length for migrations.
             *
             * @param  int  $length
             * @return void
             * @static
             */
            public static function defaultStringLength($length)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                \Illuminate\Database\Schema\MySqlBuilder::defaultStringLength($length);
            }

            /**
             * Disable foreign key constraints.
             *
             * @return bool
             * @static
             */
            public static function disableForeignKeyConstraints()
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->disableForeignKeyConstraints();
            }

            /**
             * Drop a table from the schema.
             *
             * @param  string $table
             * @return void
             * @static
             */
            public static function drop($table)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->drop($table);
            }

            /**
             * Drop all tables from the database.
             *
             * @return void
             * @static
             */
            public static function dropAllTables()
            {
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->dropAllTables();
            }

            /**
             * Drop all types from the database.
             *
             * @throws \LogicException
             * @return void
             * @static
             */
            public static function dropAllTypes()
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->dropAllTypes();
            }

            /**
             * Drop all views from the database.
             *
             * @return void
             * @static
             */
            public static function dropAllViews()
            {
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->dropAllViews();
            }

            /**
             * Drop columns from a table schema.
             *
             * @param  string       $table
             * @param  array|string $columns
             * @return void
             * @static
             */
            public static function dropColumns($table, $columns)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->dropColumns($table, $columns);
            }

            /**
             * Drop a database from the schema if the database exists.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function dropDatabaseIfExists($name)
            {
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->dropDatabaseIfExists($name);
            }

            /**
             * Drop a table from the schema if it exists.
             *
             * @param  string $table
             * @return void
             * @static
             */
            public static function dropIfExists($table)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->dropIfExists($table);
            }

            /**
             * Enable foreign key constraints.
             *
             * @return bool
             * @static
             */
            public static function enableForeignKeyConstraints()
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->enableForeignKeyConstraints();
            }

            /**
             * Get all of the table names for the database.
             *
             * @return array
             * @static
             */
            public static function getAllTables()
            {
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->getAllTables();
            }

            /**
             * Get all of the view names for the database.
             *
             * @return array
             * @static
             */
            public static function getAllViews()
            {
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->getAllViews();
            }

            /**
             * Get the column listing for a given table.
             *
             * @param  string $table
             * @return array
             * @static
             */
            public static function getColumnListing($table)
            {
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->getColumnListing($table);
            }

            /**
             * Get the data type for the given column name.
             *
             * @param  string $table
             * @param  string $column
             * @return string
             * @static
             */
            public static function getColumnType($table, $column)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->getColumnType($table, $column);
            }

            /**
             * Get the database connection instance.
             *
             * @return \Illuminate\Database\Connection
             * @static
             */
            public static function getConnection()
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->getConnection();
            }

            /**
             * Determine if the given table has a given column.
             *
             * @param  string $table
             * @param  string $column
             * @return bool
             * @static
             */
            public static function hasColumn($table, $column)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->hasColumn($table, $column);
            }

            /**
             * Determine if the given table has given columns.
             *
             * @param  string $table
             * @param  array  $columns
             * @return bool
             * @static
             */
            public static function hasColumns($table, $columns)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->hasColumns($table, $columns);
            }

            /**
             * Determine if the given table exists.
             *
             * @param  string $table
             * @return bool
             * @static
             */
            public static function hasTable($table)
            {
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->hasTable($table);
            }

            /**
             * Set the default morph key type for migrations to UUIDs.
             *
             * @return void
             * @static
             */
            public static function morphUsingUuids()
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                \Illuminate\Database\Schema\MySqlBuilder::morphUsingUuids();
            }

            /**
             * Register a custom Doctrine mapping type.
             *
             * @param  string                       $class
             * @param  string                       $name
             * @param  string                       $type
             * @throws \Doctrine\DBAL\DBALException
             * @throws \RuntimeException
             * @return void
             * @static
             */
            public static function registerCustomDoctrineType($class, $name, $type)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->registerCustomDoctrineType($class, $name, $type);
            }

            /**
             * Rename a table on the schema.
             *
             * @param  string $from
             * @param  string $to
             * @return void
             * @static
             */
            public static function rename($from, $to)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->rename($from, $to);
            }

            /**
             * Set the database connection instance.
             *
             * @param  \Illuminate\Database\Connection          $connection
             * @return \Illuminate\Database\Schema\MySqlBuilder
             * @static
             */
            public static function setConnection($connection)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                return $instance->setConnection($connection);
            }

            /**
             * Modify a table on the schema.
             *
             * @param  string   $table
             * @param  \Closure $callback
             * @return void
             * @static
             */
            public static function table($table, $callback)
            {            //Method inherited from \Illuminate\Database\Schema\Builder
                // @var \Illuminate\Database\Schema\MySqlBuilder $instance
                $instance->table($table, $callback);
            }
        }
        /**
         * @see \Illuminate\Session\SessionManager
         * @see \Illuminate\Session\Store
         */
        class Session
        {
            /**
             * Age the flash data for the session.
             *
             * @return void
             * @static
             */
            public static function ageFlashData()
            {
                // @var \Illuminate\Session\Store $instance
                $instance->ageFlashData();
            }

            /**
             * Get all of the session data.
             *
             * @return array
             * @static
             */
            public static function all()
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->all();
            }

            /**
             * Get the name of the cache store / driver that should be used to acquire session locks.
             *
             * @return string|null
             * @static
             */
            public static function blockDriver()
            {
                // @var \Illuminate\Session\SessionManager $instance
                return $instance->blockDriver();
            }

            /**
             * Decrement the value of an item in the session.
             *
             * @param  string $key
             * @param  int    $amount
             * @return int
             * @static
             */
            public static function decrement($key, $amount = 1)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->decrement($key, $amount);
            }

            /**
             * Get a driver instance.
             *
             * @param  string|null               $driver
             * @throws \InvalidArgumentException
             * @return mixed
             * @static
             */
            public static function driver($driver = null)
            {            //Method inherited from \Illuminate\Support\Manager
                // @var \Illuminate\Session\SessionManager $instance
                return $instance->driver($driver);
            }

            /**
             * Checks if a key exists.
             *
             * @param  array|string $key
             * @return bool
             * @static
             */
            public static function exists($key)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->exists($key);
            }

            /**
             * Register a custom driver creator Closure.
             *
             * @param  string                             $driver
             * @param  \Closure                           $callback
             * @return \Illuminate\Session\SessionManager
             * @static
             */
            public static function extend($driver, $callback)
            {            //Method inherited from \Illuminate\Support\Manager
                // @var \Illuminate\Session\SessionManager $instance
                return $instance->extend($driver, $callback);
            }

            /**
             * Flash a key / value pair to the session.
             *
             * @param  string $key
             * @param  mixed  $value
             * @return void
             * @static
             */
            public static function flash($key, $value = true)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->flash($key, $value);
            }

            /**
             * Flash an input array to the session.
             *
             * @param  array $value
             * @return void
             * @static
             */
            public static function flashInput($value)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->flashInput($value);
            }

            /**
             * Remove all of the items from the session.
             *
             * @return void
             * @static
             */
            public static function flush()
            {
                // @var \Illuminate\Session\Store $instance
                $instance->flush();
            }

            /**
             * Remove one or many items from the session.
             *
             * @param  array|string $keys
             * @return void
             * @static
             */
            public static function forget($keys)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->forget($keys);
            }

            /**
             * Get an item from the session.
             *
             * @param  string $key
             * @param  mixed  $default
             * @return mixed
             * @static
             */
            public static function get($key, $default = null)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->get($key, $default);
            }

            /**
             * Get the default session driver name.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Session\SessionManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Get all of the created "drivers".
             *
             * @return array
             * @static
             */
            public static function getDrivers()
            {            //Method inherited from \Illuminate\Support\Manager
                // @var \Illuminate\Session\SessionManager $instance
                return $instance->getDrivers();
            }

            /**
             * Get the underlying session handler implementation.
             *
             * @return \SessionHandlerInterface
             * @static
             */
            public static function getHandler()
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->getHandler();
            }

            /**
             * Get the current session ID.
             *
             * @return string
             * @static
             */
            public static function getId()
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->getId();
            }

            /**
             * Get the name of the session.
             *
             * @return string
             * @static
             */
            public static function getName()
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->getName();
            }

            /**
             * Get the requested item from the flashed input array.
             *
             * @param  string|null $key
             * @param  mixed       $default
             * @return mixed
             * @static
             */
            public static function getOldInput($key = null, $default = null)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->getOldInput($key, $default);
            }

            /**
             * Get the session configuration.
             *
             * @return array
             * @static
             */
            public static function getSessionConfig()
            {
                // @var \Illuminate\Session\SessionManager $instance
                return $instance->getSessionConfig();
            }

            /**
             * Determine if the session handler needs a request.
             *
             * @return bool
             * @static
             */
            public static function handlerNeedsRequest()
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->handlerNeedsRequest();
            }

            /**
             * Checks if a key is present and not null.
             *
             * @param  array|string $key
             * @return bool
             * @static
             */
            public static function has($key)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->has($key);
            }

            /**
             * Determine if the session contains old input.
             *
             * @param  string|null $key
             * @return bool
             * @static
             */
            public static function hasOldInput($key = null)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->hasOldInput($key);
            }

            /**
             * Increment the value of an item in the session.
             *
             * @param  string $key
             * @param  int    $amount
             * @return mixed
             * @static
             */
            public static function increment($key, $amount = 1)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->increment($key, $amount);
            }

            /**
             * Flush the session data and regenerate the ID.
             *
             * @return bool
             * @static
             */
            public static function invalidate()
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->invalidate();
            }

            /**
             * Determine if the session has been started.
             *
             * @return bool
             * @static
             */
            public static function isStarted()
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->isStarted();
            }

            /**
             * Determine if this is a valid session ID.
             *
             * @param  string $id
             * @return bool
             * @static
             */
            public static function isValidId($id)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->isValidId($id);
            }

            /**
             * Reflash a subset of the current flash data.
             *
             * @param  array|mixed $keys
             * @return void
             * @static
             */
            public static function keep($keys = null)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->keep($keys);
            }

            /**
             * Generate a new session ID for the session.
             *
             * @param  bool $destroy
             * @return bool
             * @static
             */
            public static function migrate($destroy = false)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->migrate($destroy);
            }

            /**
             * Flash a key / value pair to the session for immediate use.
             *
             * @param  string $key
             * @param  mixed  $value
             * @return void
             * @static
             */
            public static function now($key, $value)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->now($key, $value);
            }

            /**
             * Get a subset of the session data.
             *
             * @param  array $keys
             * @return array
             * @static
             */
            public static function only($keys)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->only($keys);
            }

            /**
             * Specify that the user has confirmed their password.
             *
             * @return void
             * @static
             */
            public static function passwordConfirmed()
            {
                // @var \Illuminate\Session\Store $instance
                $instance->passwordConfirmed();
            }

            /**
             * Get the previous URL from the session.
             *
             * @return string|null
             * @static
             */
            public static function previousUrl()
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->previousUrl();
            }

            /**
             * Get the value of a given key and then forget it.
             *
             * @param  string $key
             * @param  mixed  $default
             * @return mixed
             * @static
             */
            public static function pull($key, $default = null)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->pull($key, $default);
            }

            /**
             * Push a value onto a session array.
             *
             * @param  string $key
             * @param  mixed  $value
             * @return void
             * @static
             */
            public static function push($key, $value)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->push($key, $value);
            }

            /**
             * Put a key / value pair or array of key / value pairs in the session.
             *
             * @param  array|string $key
             * @param  mixed        $value
             * @return void
             * @static
             */
            public static function put($key, $value = null)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->put($key, $value);
            }

            /**
             * Reflash all of the session flash data.
             *
             * @return void
             * @static
             */
            public static function reflash()
            {
                // @var \Illuminate\Session\Store $instance
                $instance->reflash();
            }

            /**
             * Generate a new session identifier.
             *
             * @param  bool $destroy
             * @return bool
             * @static
             */
            public static function regenerate($destroy = false)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->regenerate($destroy);
            }

            /**
             * Regenerate the CSRF token value.
             *
             * @return void
             * @static
             */
            public static function regenerateToken()
            {
                // @var \Illuminate\Session\Store $instance
                $instance->regenerateToken();
            }

            /**
             * Get an item from the session, or store the default value.
             *
             * @param  string   $key
             * @param  \Closure $callback
             * @return mixed
             * @static
             */
            public static function remember($key, $callback)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->remember($key, $callback);
            }

            /**
             * Remove an item from the session, returning its value.
             *
             * @param  string $key
             * @return mixed
             * @static
             */
            public static function remove($key)
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->remove($key);
            }

            /**
             * Replace the given session attributes entirely.
             *
             * @param  array $attributes
             * @return void
             * @static
             */
            public static function replace($attributes)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->replace($attributes);
            }

            /**
             * Save the session data to storage.
             *
             * @return void
             * @static
             */
            public static function save()
            {
                // @var \Illuminate\Session\Store $instance
                $instance->save();
            }

            /**
             * Set the default session driver name.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function setDefaultDriver($name)
            {
                // @var \Illuminate\Session\SessionManager $instance
                $instance->setDefaultDriver($name);
            }

            /**
             * Set the existence of the session on the handler if applicable.
             *
             * @param  bool $value
             * @return void
             * @static
             */
            public static function setExists($value)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->setExists($value);
            }

            /**
             * Set the session ID.
             *
             * @param  string $id
             * @return void
             * @static
             */
            public static function setId($id)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->setId($id);
            }

            /**
             * Set the name of the session.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function setName($name)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->setName($name);
            }

            /**
             * Set the "previous" URL in the session.
             *
             * @param  string $url
             * @return void
             * @static
             */
            public static function setPreviousUrl($url)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->setPreviousUrl($url);
            }

            /**
             * Set the request on the handler instance.
             *
             * @param  \Illuminate\Http\Request $request
             * @return void
             * @static
             */
            public static function setRequestOnHandler($request)
            {
                // @var \Illuminate\Session\Store $instance
                $instance->setRequestOnHandler($request);
            }

            /**
             * Determine if requests for the same session should wait for each to finish before executing.
             *
             * @return bool
             * @static
             */
            public static function shouldBlock()
            {
                // @var \Illuminate\Session\SessionManager $instance
                return $instance->shouldBlock();
            }

            /**
             * Start the session, reading the data from a handler.
             *
             * @return bool
             * @static
             */
            public static function start()
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->start();
            }

            /**
             * Get the CSRF token value.
             *
             * @return string
             * @static
             */
            public static function token()
            {
                // @var \Illuminate\Session\Store $instance
                return $instance->token();
            }
        }
        /**
         * @see \Illuminate\Filesystem\FilesystemManager
         */
        class Storage
        {
            /**
             * Get all (recursive) of the directories within a given directory.
             *
             * @param  string|null $directory
             * @return array
             * @static
             */
            public static function allDirectories($directory = null)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->allDirectories($directory);
            }

            /**
             * Get all of the files from the given directory (recursive).
             *
             * @param  string|null $directory
             * @return array
             * @static
             */
            public static function allFiles($directory = null)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->allFiles($directory);
            }

            /**
             * Append to a file.
             *
             * @param  string $path
             * @param  string $data
             * @param  string $separator
             * @return bool
             * @static
             */
            public static function append($path, $data, $separator = '
')
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->append($path, $data, $separator);
            }

            /**
             * Assert that the given file exists.
             *
             * @param  array|string                             $path
             * @param  string|null                              $content
             * @return \Illuminate\Filesystem\FilesystemAdapter
             * @static
             */
            public static function assertExists($path, $content = null)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->assertExists($path, $content);
            }

            /**
             * Assert that the given file does not exist.
             *
             * @param  array|string                             $path
             * @return \Illuminate\Filesystem\FilesystemAdapter
             * @static
             */
            public static function assertMissing($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->assertMissing($path);
            }

            /**
             * Get a default cloud filesystem instance.
             *
             * @return \Illuminate\Filesystem\FilesystemAdapter
             * @static
             */
            public static function cloud()
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->cloud();
            }

            /**
             * Copy a file to a new location.
             *
             * @param  string $from
             * @param  string $to
             * @return bool
             * @static
             */
            public static function copy($from, $to)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->copy($from, $to);
            }

            /**
             * Create an instance of the ftp driver.
             *
             * @param  array                                    $config
             * @return \Illuminate\Filesystem\FilesystemAdapter
             * @static
             */
            public static function createFtpDriver($config)
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->createFtpDriver($config);
            }

            /**
             * Create an instance of the local driver.
             *
             * @param  array                                    $config
             * @return \Illuminate\Filesystem\FilesystemAdapter
             * @static
             */
            public static function createLocalDriver($config)
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->createLocalDriver($config);
            }

            /**
             * Create an instance of the Amazon S3 driver.
             *
             * @param  array                                  $config
             * @return \Illuminate\Contracts\Filesystem\Cloud
             * @static
             */
            public static function createS3Driver($config)
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->createS3Driver($config);
            }

            /**
             * Create an instance of the sftp driver.
             *
             * @param  array                                    $config
             * @return \Illuminate\Filesystem\FilesystemAdapter
             * @static
             */
            public static function createSftpDriver($config)
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->createSftpDriver($config);
            }

            /**
             * Delete the file at a given path.
             *
             * @param  array|string $paths
             * @return bool
             * @static
             */
            public static function delete($paths)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->delete($paths);
            }

            /**
             * Recursively delete a directory.
             *
             * @param  string $directory
             * @return bool
             * @static
             */
            public static function deleteDirectory($directory)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->deleteDirectory($directory);
            }

            /**
             * Get all of the directories within a given directory.
             *
             * @param  string|null $directory
             * @param  bool        $recursive
             * @return array
             * @static
             */
            public static function directories($directory = null, $recursive = false)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->directories($directory, $recursive);
            }

            /**
             * Get a filesystem instance.
             *
             * @param  string|null                              $name
             * @return \Illuminate\Filesystem\FilesystemAdapter
             * @static
             */
            public static function disk($name = null)
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->disk($name);
            }

            /**
             * Create a streamed download response for a given file.
             *
             * @param  string                                             $path
             * @param  string|null                                        $name
             * @param  array|null                                         $headers
             * @return \Symfony\Component\HttpFoundation\StreamedResponse
             * @static
             */
            public static function download($path, $name = null, $headers = [])
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->download($path, $name, $headers);
            }

            /**
             * Get a filesystem instance.
             *
             * @param  string|null                              $name
             * @return \Illuminate\Filesystem\FilesystemAdapter
             * @static
             */
            public static function drive($name = null)
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->drive($name);
            }

            /**
             * Determine if a file exists.
             *
             * @param  string $path
             * @return bool
             * @static
             */
            public static function exists($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->exists($path);
            }

            /**
             * Register a custom driver creator Closure.
             *
             * @param  string                                   $driver
             * @param  \Closure                                 $callback
             * @return \Illuminate\Filesystem\FilesystemManager
             * @static
             */
            public static function extend($driver, $callback)
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->extend($driver, $callback);
            }

            /**
             * Get an array of all files in a directory.
             *
             * @param  string|null $directory
             * @param  bool        $recursive
             * @return array
             * @static
             */
            public static function files($directory = null, $recursive = false)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->files($directory, $recursive);
            }

            /**
             * Flush the Flysystem cache.
             *
             * @return void
             * @static
             */
            public static function flushCache()
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                $instance->flushCache();
            }

            /**
             * Unset the given disk instances.
             *
             * @param  array|string                             $disk
             * @return \Illuminate\Filesystem\FilesystemManager
             * @static
             */
            public static function forgetDisk($disk)
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->forgetDisk($disk);
            }

            /**
             * Get the contents of a file.
             *
             * @param  string                                                 $path
             * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
             * @return string
             * @static
             */
            public static function get($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->get($path);
            }

            /**
             * Get a temporary URL for the file at the given path.
             *
             * @param  \League\Flysystem\AwsS3v3\AwsS3Adapter $adapter
             * @param  string                                 $path
             * @param  \DateTimeInterface                     $expiration
             * @param  array                                  $options
             * @return string
             * @static
             */
            public static function getAwsTemporaryUrl($adapter, $path, $expiration, $options)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->getAwsTemporaryUrl($adapter, $path, $expiration, $options);
            }

            /**
             * Get the default cloud driver name.
             *
             * @return string
             * @static
             */
            public static function getDefaultCloudDriver()
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->getDefaultCloudDriver();
            }

            /**
             * Get the default driver name.
             *
             * @return string
             * @static
             */
            public static function getDefaultDriver()
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->getDefaultDriver();
            }

            /**
             * Get the Flysystem driver.
             *
             * @return \League\Flysystem\FilesystemInterface
             * @static
             */
            public static function getDriver()
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->getDriver();
            }

            /**
             * Get the visibility for the given path.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function getVisibility($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->getVisibility($path);
            }

            /**
             * Get the file's last modification time.
             *
             * @param  string $path
             * @return int
             * @static
             */
            public static function lastModified($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->lastModified($path);
            }

            /**
             * Create a directory.
             *
             * @param  string $path
             * @return bool
             * @static
             */
            public static function makeDirectory($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->makeDirectory($path);
            }

            /**
             * Get the mime-type of a given file.
             *
             * @param  string       $path
             * @return false|string
             * @static
             */
            public static function mimeType($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->mimeType($path);
            }

            /**
             * Determine if a file or directory is missing.
             *
             * @param  string $path
             * @return bool
             * @static
             */
            public static function missing($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->missing($path);
            }

            /**
             * Move a file to a new location.
             *
             * @param  string $from
             * @param  string $to
             * @return bool
             * @static
             */
            public static function move($from, $to)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->move($from, $to);
            }

            /**
             * Get the full path for the file at the given "short" path.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function path($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->path($path);
            }

            /**
             * Prepend to a file.
             *
             * @param  string $path
             * @param  string $data
             * @param  string $separator
             * @return bool
             * @static
             */
            public static function prepend($path, $data, $separator = '
')
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->prepend($path, $data, $separator);
            }

            /**
             * Disconnect the given disk and remove from local cache.
             *
             * @param  string|null $name
             * @return void
             * @static
             */
            public static function purge($name = null)
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                $instance->purge($name);
            }

            /**
             * Write the contents of a file.
             *
             * @param  string          $path
             * @param  resource|string $contents
             * @param  mixed           $options
             * @return bool
             * @static
             */
            public static function put($path, $contents, $options = [])
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->put($path, $contents, $options);
            }

            /**
             * Store the uploaded file on the disk.
             *
             * @param  string                                                     $path
             * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string $file
             * @param  mixed                                                      $options
             * @return false|string
             * @static
             */
            public static function putFile($path, $file, $options = [])
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->putFile($path, $file, $options);
            }

            /**
             * Store the uploaded file on the disk with a given name.
             *
             * @param  string                                                     $path
             * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string $file
             * @param  string                                                     $name
             * @param  mixed                                                      $options
             * @return false|string
             * @static
             */
            public static function putFileAs($path, $file, $name, $options = [])
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->putFileAs($path, $file, $name, $options);
            }

            /**
             * Get a resource to read the file.
             *
             * @param  string                                                 $path
             * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
             * @return resource|null                                          the path resource or null on failure
             * @static
             */
            public static function readStream($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->readStream($path);
            }

            /**
             * Create a streamed response for a given file.
             *
             * @param  string                                             $path
             * @param  string|null                                        $name
             * @param  array|null                                         $headers
             * @param  string|null                                        $disposition
             * @return \Symfony\Component\HttpFoundation\StreamedResponse
             * @static
             */
            public static function response($path, $name = null, $headers = [], $disposition = 'inline')
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->response($path, $name, $headers, $disposition);
            }

            /**
             * Set the given disk instance.
             *
             * @param  string                                   $name
             * @param  mixed                                    $disk
             * @return \Illuminate\Filesystem\FilesystemManager
             * @static
             */
            public static function set($name, $disk)
            {
                // @var \Illuminate\Filesystem\FilesystemManager $instance
                return $instance->set($name, $disk);
            }

            /**
             * Set the visibility for the given path.
             *
             * @param  string $path
             * @param  string $visibility
             * @return bool
             * @static
             */
            public static function setVisibility($path, $visibility)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->setVisibility($path, $visibility);
            }

            /**
             * Get the file size of a given file.
             *
             * @param  string $path
             * @return int
             * @static
             */
            public static function size($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->size($path);
            }

            /**
             * Get a temporary URL for the file at the given path.
             *
             * @param  string             $path
             * @param  \DateTimeInterface $expiration
             * @param  array              $options
             * @throws \RuntimeException
             * @return string
             * @static
             */
            public static function temporaryUrl($path, $expiration, $options = [])
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->temporaryUrl($path, $expiration, $options);
            }

            /**
             * Get the URL for the file at the given path.
             *
             * @param  string            $path
             * @throws \RuntimeException
             * @return string
             * @static
             */
            public static function url($path)
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->url($path);
            }

            /**
             * Write a new file using a stream.
             *
             * @param  string                                               $path
             * @param  resource                                             $resource
             * @param  array                                                $options
             * @throws \InvalidArgumentException                            if $resource is not a file handle
             * @throws \Illuminate\Contracts\Filesystem\FileExistsException
             * @return bool
             * @static
             */
            public static function writeStream($path, $resource, $options = [])
            {
                // @var \Illuminate\Filesystem\FilesystemAdapter $instance
                return $instance->writeStream($path, $resource, $options);
            }
        }
        /**
         * @see \Illuminate\Routing\UrlGenerator
         */
        class URL
        {
            /**
             * Get the URL to a controller action.
             *
             * @param  array|string              $action
             * @param  mixed                     $parameters
             * @param  bool                      $absolute
             * @throws \InvalidArgumentException
             * @return string
             * @static
             */
            public static function action($action, $parameters = [], $absolute = true)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->action($action, $parameters, $absolute);
            }

            /**
             * Generate the URL to an application asset.
             *
             * @param  string    $path
             * @param  bool|null $secure
             * @return string
             * @static
             */
            public static function asset($path, $secure = null)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->asset($path, $secure);
            }

            /**
             * Generate the URL to an asset from a custom root domain such as CDN, etc.
             *
             * @param  string    $root
             * @param  string    $path
             * @param  bool|null $secure
             * @return string
             * @static
             */
            public static function assetFrom($root, $path, $secure = null)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->assetFrom($root, $path, $secure);
            }

            /**
             * Get the current URL for the request.
             *
             * @return string
             * @static
             */
            public static function current()
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->current();
            }

            /**
             * Set the default named parameters used by the URL generator.
             *
             * @param  array $defaults
             * @return void
             * @static
             */
            public static function defaults($defaults)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                $instance->defaults($defaults);
            }

            /**
             * Set the forced root URL.
             *
             * @param  string|null $root
             * @return void
             * @static
             */
            public static function forceRootUrl($root)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                $instance->forceRootUrl($root);
            }

            /**
             * Force the scheme for URLs.
             *
             * @param  string|null $scheme
             * @return void
             * @static
             */
            public static function forceScheme($scheme)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                $instance->forceScheme($scheme);
            }

            /**
             * Format the given URL segments into a single URL.
             *
             * @param  string                         $root
             * @param  string                         $path
             * @param  \Illuminate\Routing\Route|null $route
             * @return string
             * @static
             */
            public static function format($root, $path, $route = null)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->format($root, $path, $route);
            }

            /**
             * Set a callback to be used to format the host of generated URLs.
             *
             * @param  \Closure                         $callback
             * @return \Illuminate\Routing\UrlGenerator
             * @static
             */
            public static function formatHostUsing($callback)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->formatHostUsing($callback);
            }

            /**
             * Format the array of URL parameters.
             *
             * @param  array|mixed $parameters
             * @return array
             * @static
             */
            public static function formatParameters($parameters)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->formatParameters($parameters);
            }

            /**
             * Set a callback to be used to format the path of generated URLs.
             *
             * @param  \Closure                         $callback
             * @return \Illuminate\Routing\UrlGenerator
             * @static
             */
            public static function formatPathUsing($callback)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->formatPathUsing($callback);
            }

            /**
             * Get the base URL for the request.
             *
             * @param  string      $scheme
             * @param  string|null $root
             * @return string
             * @static
             */
            public static function formatRoot($scheme, $root = null)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->formatRoot($scheme, $root);
            }

            /**
             * Get the default scheme for a raw URL.
             *
             * @param  bool|null $secure
             * @return string
             * @static
             */
            public static function formatScheme($secure = null)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->formatScheme($secure);
            }

            /**
             * Get the full URL for the current request.
             *
             * @return string
             * @static
             */
            public static function full()
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->full();
            }

            /**
             * Get the default named parameters used by the URL generator.
             *
             * @return array
             * @static
             */
            public static function getDefaultParameters()
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->getDefaultParameters();
            }

            /**
             * Get the request instance.
             *
             * @return \Illuminate\Http\Request
             * @static
             */
            public static function getRequest()
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->getRequest();
            }

            /**
             * Determine if the signature from the given request matches the URL.
             *
             * @param  \Illuminate\Http\Request $request
             * @param  bool                     $absolute
             * @return bool
             * @static
             */
            public static function hasCorrectSignature($request, $absolute = true)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->hasCorrectSignature($request, $absolute);
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\Routing\UrlGenerator::hasMacro($name);
            }

            /**
             * Determine if the given request has a valid signature for a relative URL.
             *
             * @param  \Illuminate\Http\Request $request
             * @return bool
             * @static
             */
            public static function hasValidRelativeSignature($request)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->hasValidRelativeSignature($request);
            }

            /**
             * Determine if the given request has a valid signature.
             *
             * @param  \Illuminate\Http\Request $request
             * @param  bool                     $absolute
             * @return bool
             * @static
             */
            public static function hasValidSignature($request, $absolute = true)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->hasValidSignature($request, $absolute);
            }

            /**
             * Determine if the given path is a valid URL.
             *
             * @param  string $path
             * @return bool
             * @static
             */
            public static function isValidUrl($path)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->isValidUrl($path);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\Routing\UrlGenerator::macro($name, $macro);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\Routing\UrlGenerator::mixin($mixin, $replace);
            }

            /**
             * Get the path formatter being used by the URL generator.
             *
             * @return \Closure
             * @static
             */
            public static function pathFormatter()
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->pathFormatter();
            }

            /**
             * Get the URL for the previous request.
             *
             * @param  mixed  $fallback
             * @return string
             * @static
             */
            public static function previous($fallback = false)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->previous($fallback);
            }

            /**
             * Get the URL to a named route.
             *
             * @param  string                                                      $name
             * @param  mixed                                                       $parameters
             * @param  bool                                                        $absolute
             * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
             * @return string
             * @static
             */
            public static function route($name, $parameters = [], $absolute = true)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->route($name, $parameters, $absolute);
            }

            /**
             * Generate a secure, absolute URL to the given path.
             *
             * @param  string $path
             * @param  array  $parameters
             * @return string
             * @static
             */
            public static function secure($path, $parameters = [])
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->secure($path, $parameters);
            }

            /**
             * Generate the URL to a secure asset.
             *
             * @param  string $path
             * @return string
             * @static
             */
            public static function secureAsset($path)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->secureAsset($path);
            }

            /**
             * Set the encryption key resolver.
             *
             * @param  callable                         $keyResolver
             * @return \Illuminate\Routing\UrlGenerator
             * @static
             */
            public static function setKeyResolver($keyResolver)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->setKeyResolver($keyResolver);
            }

            /**
             * Set the current request instance.
             *
             * @param  \Illuminate\Http\Request $request
             * @return void
             * @static
             */
            public static function setRequest($request)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                $instance->setRequest($request);
            }

            /**
             * Set the root controller namespace.
             *
             * @param  string                           $rootNamespace
             * @return \Illuminate\Routing\UrlGenerator
             * @static
             */
            public static function setRootControllerNamespace($rootNamespace)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->setRootControllerNamespace($rootNamespace);
            }

            /**
             * Set the route collection.
             *
             * @param  \Illuminate\Routing\RouteCollectionInterface $routes
             * @return \Illuminate\Routing\UrlGenerator
             * @static
             */
            public static function setRoutes($routes)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->setRoutes($routes);
            }

            /**
             * Set the session resolver for the generator.
             *
             * @param  callable                         $sessionResolver
             * @return \Illuminate\Routing\UrlGenerator
             * @static
             */
            public static function setSessionResolver($sessionResolver)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->setSessionResolver($sessionResolver);
            }

            /**
             * Determine if the expires timestamp from the given request is not from the past.
             *
             * @param  \Illuminate\Http\Request $request
             * @return bool
             * @static
             */
            public static function signatureHasNotExpired($request)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->signatureHasNotExpired($request);
            }

            /**
             * Create a signed route URL for a named route.
             *
             * @param  string                                    $name
             * @param  mixed                                     $parameters
             * @param  \DateInterval|\DateTimeInterface|int|null $expiration
             * @param  bool                                      $absolute
             * @throws \InvalidArgumentException
             * @return string
             * @static
             */
            public static function signedRoute($name, $parameters = [], $expiration = null, $absolute = true)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->signedRoute($name, $parameters, $expiration, $absolute);
            }

            /**
             * Create a temporary signed route URL for a named route.
             *
             * @param  string                               $name
             * @param  \DateInterval|\DateTimeInterface|int $expiration
             * @param  array                                $parameters
             * @param  bool                                 $absolute
             * @return string
             * @static
             */
            public static function temporarySignedRoute($name, $expiration, $parameters = [], $absolute = true)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->temporarySignedRoute($name, $expiration, $parameters, $absolute);
            }

            /**
             * Generate an absolute URL to the given path.
             *
             * @param  string    $path
             * @param  mixed     $extra
             * @param  bool|null $secure
             * @return string
             * @static
             */
            public static function to($path, $extra = [], $secure = null)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->to($path, $extra, $secure);
            }

            /**
             * Get the URL for a given route instance.
             *
             * @param  \Illuminate\Routing\Route                             $route
             * @param  mixed                                                 $parameters
             * @param  bool                                                  $absolute
             * @throws \Illuminate\Routing\Exceptions\UrlGenerationException
             * @return string
             * @static
             */
            public static function toRoute($route, $parameters, $absolute)
            {
                // @var \Illuminate\Routing\UrlGenerator $instance
                return $instance->toRoute($route, $parameters, $absolute);
            }
        }
        /**
         * @see \Illuminate\Validation\Factory
         */
        class Validator
        {
            /**
             * Register a custom validator extension.
             *
             * @param  string          $rule
             * @param  \Closure|string $extension
             * @param  string|null     $message
             * @return void
             * @static
             */
            public static function extend($rule, $extension, $message = null)
            {
                // @var \Illuminate\Validation\Factory $instance
                $instance->extend($rule, $extension, $message);
            }

            /**
             * Register a custom dependent validator extension.
             *
             * @param  string          $rule
             * @param  \Closure|string $extension
             * @param  string|null     $message
             * @return void
             * @static
             */
            public static function extendDependent($rule, $extension, $message = null)
            {
                // @var \Illuminate\Validation\Factory $instance
                $instance->extendDependent($rule, $extension, $message);
            }

            /**
             * Register a custom implicit validator extension.
             *
             * @param  string          $rule
             * @param  \Closure|string $extension
             * @param  string|null     $message
             * @return void
             * @static
             */
            public static function extendImplicit($rule, $extension, $message = null)
            {
                // @var \Illuminate\Validation\Factory $instance
                $instance->extendImplicit($rule, $extension, $message);
            }

            /**
             * Get the Presence Verifier implementation.
             *
             * @return \Illuminate\Validation\PresenceVerifierInterface
             * @static
             */
            public static function getPresenceVerifier()
            {
                // @var \Illuminate\Validation\Factory $instance
                return $instance->getPresenceVerifier();
            }

            /**
             * Get the Translator implementation.
             *
             * @return \Illuminate\Contracts\Translation\Translator
             * @static
             */
            public static function getTranslator()
            {
                // @var \Illuminate\Validation\Factory $instance
                return $instance->getTranslator();
            }

            /**
             * Create a new Validator instance.
             *
             * @param  array                            $data
             * @param  array                            $rules
             * @param  array                            $messages
             * @param  array                            $customAttributes
             * @return \Illuminate\Validation\Validator
             * @static
             */
            public static function make($data, $rules, $messages = [], $customAttributes = [])
            {
                // @var \Illuminate\Validation\Factory $instance
                return $instance->make($data, $rules, $messages, $customAttributes);
            }

            /**
             * Register a custom validator message replacer.
             *
             * @param  string          $rule
             * @param  \Closure|string $replacer
             * @return void
             * @static
             */
            public static function replacer($rule, $replacer)
            {
                // @var \Illuminate\Validation\Factory $instance
                $instance->replacer($rule, $replacer);
            }

            /**
             * Set the Validator instance resolver.
             *
             * @param  \Closure $resolver
             * @return void
             * @static
             */
            public static function resolver($resolver)
            {
                // @var \Illuminate\Validation\Factory $instance
                $instance->resolver($resolver);
            }

            /**
             * Set the Presence Verifier implementation.
             *
             * @param  \Illuminate\Validation\PresenceVerifierInterface $presenceVerifier
             * @return void
             * @static
             */
            public static function setPresenceVerifier($presenceVerifier)
            {
                // @var \Illuminate\Validation\Factory $instance
                $instance->setPresenceVerifier($presenceVerifier);
            }

            /**
             * Validate the given data against the provided rules.
             *
             * @param  array                                      $data
             * @param  array                                      $rules
             * @param  array                                      $messages
             * @param  array                                      $customAttributes
             * @throws \Illuminate\Validation\ValidationException
             * @return array
             * @static
             */
            public static function validate($data, $rules, $messages = [], $customAttributes = [])
            {
                // @var \Illuminate\Validation\Factory $instance
                return $instance->validate($data, $rules, $messages, $customAttributes);
            }
        }
        /**
         * @see \Illuminate\View\Factory
         */
        class View
        {
            /**
             * Register a valid view extension and its engine.
             *
             * @param  string        $extension
             * @param  string        $engine
             * @param  \Closure|null $resolver
             * @return void
             * @static
             */
            public static function addExtension($extension, $engine, $resolver = null)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->addExtension($extension, $engine, $resolver);
            }

            /**
             * Add a location to the array of view locations.
             *
             * @param  string $location
             * @return void
             * @static
             */
            public static function addLocation($location)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->addLocation($location);
            }

            /**
             * Add new loop to the stack.
             *
             * @param  array|\Countable $data
             * @return void
             * @static
             */
            public static function addLoop($data)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->addLoop($data);
            }

            /**
             * Add a new namespace to the loader.
             *
             * @param  string                   $namespace
             * @param  array|string             $hints
             * @return \Illuminate\View\Factory
             * @static
             */
            public static function addNamespace($namespace, $hints)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->addNamespace($namespace, $hints);
            }

            /**
             * Stop injecting content into a section and append it.
             *
             * @throws \InvalidArgumentException
             * @return string
             * @static
             */
            public static function appendSection()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->appendSection();
            }

            /**
             * Call the composer for a given view.
             *
             * @param  \Illuminate\Contracts\View\View $view
             * @return void
             * @static
             */
            public static function callComposer($view)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->callComposer($view);
            }

            /**
             * Call the creator for a given view.
             *
             * @param  \Illuminate\Contracts\View\View $view
             * @return void
             * @static
             */
            public static function callCreator($view)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->callCreator($view);
            }

            /**
             * Register a view composer event.
             *
             * @param  array|string    $views
             * @param  \Closure|string $callback
             * @return array
             * @static
             */
            public static function composer($views, $callback)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->composer($views, $callback);
            }

            /**
             * Register multiple view composers via an array.
             *
             * @param  array $composers
             * @return array
             * @static
             */
            public static function composers($composers)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->composers($composers);
            }

            /**
             * Register a view creator event.
             *
             * @param  array|string    $views
             * @param  \Closure|string $callback
             * @return array
             * @static
             */
            public static function creator($views, $callback)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->creator($views, $callback);
            }

            /**
             * Decrement the rendering counter.
             *
             * @return void
             * @static
             */
            public static function decrementRender()
            {
                // @var \Illuminate\View\Factory $instance
                $instance->decrementRender();
            }

            /**
             * Check if there are no active render operations.
             *
             * @return bool
             * @static
             */
            public static function doneRendering()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->doneRendering();
            }

            /**
             * Save the slot content for rendering.
             *
             * @return void
             * @static
             */
            public static function endSlot()
            {
                // @var \Illuminate\View\Factory $instance
                $instance->endSlot();
            }

            /**
             * Determine if a given view exists.
             *
             * @param  string $view
             * @return bool
             * @static
             */
            public static function exists($view)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->exists($view);
            }

            /**
             * Get the evaluated view contents for the given view.
             *
             * @param  string                                        $path
             * @param  array|\Illuminate\Contracts\Support\Arrayable $data
             * @param  array                                         $mergeData
             * @return \Illuminate\Contracts\View\View
             * @static
             */
            public static function file($path, $data = [], $mergeData = [])
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->file($path, $data, $mergeData);
            }

            /**
             * Get the first view that actually exists from the given list.
             *
             * @param  array                                         $views
             * @param  array|\Illuminate\Contracts\Support\Arrayable $data
             * @param  array                                         $mergeData
             * @throws \InvalidArgumentException
             * @return \Illuminate\Contracts\View\View
             * @static
             */
            public static function first($views, $data = [], $mergeData = [])
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->first($views, $data, $mergeData);
            }

            /**
             * Flush the cache of views located by the finder.
             *
             * @return void
             * @static
             */
            public static function flushFinderCache()
            {
                // @var \Illuminate\View\Factory $instance
                $instance->flushFinderCache();
            }

            /**
             * Flush all of the sections.
             *
             * @return void
             * @static
             */
            public static function flushSections()
            {
                // @var \Illuminate\View\Factory $instance
                $instance->flushSections();
            }

            /**
             * Flush all of the stacks.
             *
             * @return void
             * @static
             */
            public static function flushStacks()
            {
                // @var \Illuminate\View\Factory $instance
                $instance->flushStacks();
            }

            /**
             * Flush all of the factory state like sections and stacks.
             *
             * @return void
             * @static
             */
            public static function flushState()
            {
                // @var \Illuminate\View\Factory $instance
                $instance->flushState();
            }

            /**
             * Flush all of the section contents if done rendering.
             *
             * @return void
             * @static
             */
            public static function flushStateIfDoneRendering()
            {
                // @var \Illuminate\View\Factory $instance
                $instance->flushStateIfDoneRendering();
            }

            /**
             * Get the IoC container instance.
             *
             * @return \Illuminate\Contracts\Container\Container
             * @static
             */
            public static function getContainer()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getContainer();
            }

            /**
             * Get the event dispatcher instance.
             *
             * @return \Illuminate\Contracts\Events\Dispatcher
             * @static
             */
            public static function getDispatcher()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getDispatcher();
            }

            /**
             * Get the appropriate view engine for the given path.
             *
             * @param  string                            $path
             * @throws \InvalidArgumentException
             * @return \Illuminate\Contracts\View\Engine
             * @static
             */
            public static function getEngineFromPath($path)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getEngineFromPath($path);
            }

            /**
             * Get the engine resolver instance.
             *
             * @return \Illuminate\View\Engines\EngineResolver
             * @static
             */
            public static function getEngineResolver()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getEngineResolver();
            }

            /**
             * Get the extension to engine bindings.
             *
             * @return array
             * @static
             */
            public static function getExtensions()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getExtensions();
            }

            /**
             * Get the view finder instance.
             *
             * @return \Illuminate\View\ViewFinderInterface
             * @static
             */
            public static function getFinder()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getFinder();
            }

            /**
             * Get an instance of the last loop in the stack.
             *
             * @return \stdClass|null
             * @static
             */
            public static function getLastLoop()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getLastLoop();
            }

            /**
             * Get the entire loop stack.
             *
             * @return array
             * @static
             */
            public static function getLoopStack()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getLoopStack();
            }

            /**
             * Get the contents of a section.
             *
             * @param  string      $name
             * @param  string|null $default
             * @return mixed
             * @static
             */
            public static function getSection($name, $default = null)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getSection($name, $default);
            }

            /**
             * Get the entire array of sections.
             *
             * @return array
             * @static
             */
            public static function getSections()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getSections();
            }

            /**
             * Get all of the shared data for the environment.
             *
             * @return array
             * @static
             */
            public static function getShared()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->getShared();
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Illuminate\View\Factory::hasMacro($name);
            }

            /**
             * Determine if the given once token has been rendered.
             *
             * @param  string $id
             * @return bool
             * @static
             */
            public static function hasRenderedOnce($id)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->hasRenderedOnce($id);
            }

            /**
             * Check if the section exists.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasSection($name)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->hasSection($name);
            }

            /**
             * Increment the top loop's indices.
             *
             * @return void
             * @static
             */
            public static function incrementLoopIndices()
            {
                // @var \Illuminate\View\Factory $instance
                $instance->incrementLoopIndices();
            }

            /**
             * Increment the rendering counter.
             *
             * @return void
             * @static
             */
            public static function incrementRender()
            {
                // @var \Illuminate\View\Factory $instance
                $instance->incrementRender();
            }

            /**
             * Inject inline content into a section.
             *
             * @param  string $section
             * @param  string $content
             * @return void
             * @static
             */
            public static function inject($section, $content)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->inject($section, $content);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Illuminate\View\Factory::macro($name, $macro);
            }

            /**
             * Get the evaluated view contents for the given view.
             *
             * @param  string                                        $view
             * @param  array|\Illuminate\Contracts\Support\Arrayable $data
             * @param  array                                         $mergeData
             * @return \Illuminate\Contracts\View\View
             * @static
             */
            public static function make($view, $data = [], $mergeData = [])
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->make($view, $data, $mergeData);
            }

            /**
             * Mark the given once token as having been rendered.
             *
             * @param  string $id
             * @return void
             * @static
             */
            public static function markAsRenderedOnce($id)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->markAsRenderedOnce($id);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Illuminate\View\Factory::mixin($mixin, $replace);
            }

            /**
             * Get the parent placeholder for the current request.
             *
             * @param  string $section
             * @return string
             * @static
             */
            public static function parentPlaceholder($section = '')
            {
                return \Illuminate\View\Factory::parentPlaceholder($section);
            }

            /**
             * Pop a loop from the top of the loop stack.
             *
             * @return void
             * @static
             */
            public static function popLoop()
            {
                // @var \Illuminate\View\Factory $instance
                $instance->popLoop();
            }

            /**
             * Prepend a new namespace to the loader.
             *
             * @param  string                   $namespace
             * @param  array|string             $hints
             * @return \Illuminate\View\Factory
             * @static
             */
            public static function prependNamespace($namespace, $hints)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->prependNamespace($namespace, $hints);
            }

            /**
             * Render the current component.
             *
             * @return string
             * @static
             */
            public static function renderComponent()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->renderComponent();
            }

            /**
             * Get the rendered contents of a partial from a loop.
             *
             * @param  string $view
             * @param  array  $data
             * @param  string $iterator
             * @param  string $empty
             * @return string
             * @static
             */
            public static function renderEach($view, $data, $iterator, $empty = 'raw|')
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->renderEach($view, $data, $iterator, $empty);
            }

            /**
             * Render the current translation.
             *
             * @return string
             * @static
             */
            public static function renderTranslation()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->renderTranslation();
            }

            /**
             * Get the rendered content of the view based on a given condition.
             *
             * @param  bool                                          $condition
             * @param  string                                        $view
             * @param  array|\Illuminate\Contracts\Support\Arrayable $data
             * @param  array                                         $mergeData
             * @return string
             * @static
             */
            public static function renderWhen($condition, $view, $data = [], $mergeData = [])
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->renderWhen($condition, $view, $data, $mergeData);
            }

            /**
             * Replace the namespace hints for the given namespace.
             *
             * @param  string                   $namespace
             * @param  array|string             $hints
             * @return \Illuminate\View\Factory
             * @static
             */
            public static function replaceNamespace($namespace, $hints)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->replaceNamespace($namespace, $hints);
            }

            /**
             * Check if section does not exist.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function sectionMissing($name)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->sectionMissing($name);
            }

            /**
             * Set the IoC container instance.
             *
             * @param  \Illuminate\Contracts\Container\Container $container
             * @return void
             * @static
             */
            public static function setContainer($container)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->setContainer($container);
            }

            /**
             * Set the event dispatcher instance.
             *
             * @param  \Illuminate\Contracts\Events\Dispatcher $events
             * @return void
             * @static
             */
            public static function setDispatcher($events)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->setDispatcher($events);
            }

            /**
             * Set the view finder instance.
             *
             * @param  \Illuminate\View\ViewFinderInterface $finder
             * @return void
             * @static
             */
            public static function setFinder($finder)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->setFinder($finder);
            }

            /**
             * Add a piece of shared data to the environment.
             *
             * @param  array|string $key
             * @param  mixed|null   $value
             * @return mixed
             * @static
             */
            public static function share($key, $value = null)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->share($key, $value);
            }

            /**
             * Get an item from the shared data.
             *
             * @param  string $key
             * @param  mixed  $default
             * @return mixed
             * @static
             */
            public static function shared($key, $default = null)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->shared($key, $default);
            }

            /**
             * Start the slot rendering process.
             *
             * @param  string      $name
             * @param  string|null $content
             * @return void
             * @static
             */
            public static function slot($name, $content = null)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->slot($name, $content);
            }

            /**
             * Start a component rendering process.
             *
             * @param  \Closure|\Illuminate\Contracts\Support\Htmlable|\Illuminate\Contracts\View\View|string $view
             * @param  array                                                                                  $data
             * @return void
             * @static
             */
            public static function startComponent($view, $data = [])
            {
                // @var \Illuminate\View\Factory $instance
                $instance->startComponent($view, $data);
            }

            /**
             * Get the first view that actually exists from the given list, and start a component.
             *
             * @param  array $names
             * @param  array $data
             * @return void
             * @static
             */
            public static function startComponentFirst($names, $data = [])
            {
                // @var \Illuminate\View\Factory $instance
                $instance->startComponentFirst($names, $data);
            }

            /**
             * Start prepending content into a push section.
             *
             * @param  string $section
             * @param  string $content
             * @return void
             * @static
             */
            public static function startPrepend($section, $content = '')
            {
                // @var \Illuminate\View\Factory $instance
                $instance->startPrepend($section, $content);
            }

            /**
             * Start injecting content into a push section.
             *
             * @param  string $section
             * @param  string $content
             * @return void
             * @static
             */
            public static function startPush($section, $content = '')
            {
                // @var \Illuminate\View\Factory $instance
                $instance->startPush($section, $content);
            }

            /**
             * Start injecting content into a section.
             *
             * @param  string      $section
             * @param  string|null $content
             * @return void
             * @static
             */
            public static function startSection($section, $content = null)
            {
                // @var \Illuminate\View\Factory $instance
                $instance->startSection($section, $content);
            }

            /**
             * Start a translation block.
             *
             * @param  array $replacements
             * @return void
             * @static
             */
            public static function startTranslation($replacements = [])
            {
                // @var \Illuminate\View\Factory $instance
                $instance->startTranslation($replacements);
            }

            /**
             * Stop prepending content into a push section.
             *
             * @throws \InvalidArgumentException
             * @return string
             * @static
             */
            public static function stopPrepend()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->stopPrepend();
            }

            /**
             * Stop injecting content into a push section.
             *
             * @throws \InvalidArgumentException
             * @return string
             * @static
             */
            public static function stopPush()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->stopPush();
            }

            /**
             * Stop injecting content into a section.
             *
             * @param  bool                      $overwrite
             * @throws \InvalidArgumentException
             * @return string
             * @static
             */
            public static function stopSection($overwrite = false)
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->stopSection($overwrite);
            }

            /**
             * Get the string contents of a section.
             *
             * @param  string $section
             * @param  string $default
             * @return string
             * @static
             */
            public static function yieldContent($section, $default = '')
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->yieldContent($section, $default);
            }

            /**
             * Get the string contents of a push section.
             *
             * @param  string $section
             * @param  string $default
             * @return string
             * @static
             */
            public static function yieldPushContent($section, $default = '')
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->yieldPushContent($section, $default);
            }

            /**
             * Stop injecting content into a section and return its contents.
             *
             * @return string
             * @static
             */
            public static function yieldSection()
            {
                // @var \Illuminate\View\Factory $instance
                return $instance->yieldSection();
            }
        }
}

namespace GrofGraf\LaravelPDFMerger\Facades {
        class PDFMergerFacade
        {
            /**
             * Clear a resolved facade instance.
             *
             * @param  string $name
             * @return void
             * @static
             */
            public static function clearResolvedInstance($name)
            {            //Method inherited from \Illuminate\Support\Facades\Facade
                \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade::clearResolvedInstance($name);
            }

            /**
             * Clear all of the resolved instances.
             *
             * @return void
             * @static
             */
            public static function clearResolvedInstances()
            {            //Method inherited from \Illuminate\Support\Facades\Facade
                \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade::clearResolvedInstances();
            }

            /**
             * Get the application instance behind the facade.
             *
             * @return \Illuminate\Contracts\Foundation\Application
             * @static
             */
            public static function getFacadeApplication()
            {            //Method inherited from \Illuminate\Support\Facades\Facade
                return \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade::getFacadeApplication();
            }

            /**
             * Get the root object behind the facade.
             *
             * @return mixed
             * @static
             */
            public static function getFacadeRoot()
            {            //Method inherited from \Illuminate\Support\Facades\Facade
                return \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade::getFacadeRoot();
            }

            /**
             * Initiate a partial mock on the facade.
             *
             * @return \Mockery\MockInterface
             * @static
             */
            public static function partialMock()
            {            //Method inherited from \Illuminate\Support\Facades\Facade
                return \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade::partialMock();
            }

            /**
             * Run a Closure when the facade has been resolved.
             *
             * @param  \Closure $callback
             * @return void
             * @static
             */
            public static function resolved($callback)
            {            //Method inherited from \Illuminate\Support\Facades\Facade
                \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade::resolved($callback);
            }

            /**
             * Set the application instance.
             *
             * @param  \Illuminate\Contracts\Foundation\Application $app
             * @return void
             * @static
             */
            public static function setFacadeApplication($app)
            {            //Method inherited from \Illuminate\Support\Facades\Facade
                \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade::setFacadeApplication($app);
            }

            /**
             * Initiate a mock expectation on the facade.
             *
             * @return \Mockery\Expectation
             * @static
             */
            public static function shouldReceive()
            {            //Method inherited from \Illuminate\Support\Facades\Facade
                return \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade::shouldReceive();
            }

            /**
             * Convert the facade into a Mockery spy.
             *
             * @return \Mockery\MockInterface
             * @static
             */
            public static function spy()
            {            //Method inherited from \Illuminate\Support\Facades\Facade
                return \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade::spy();
            }

            /**
             * Hotswap the underlying instance behind the facade.
             *
             * @param  mixed $instance
             * @return void
             * @static
             */
            public static function swap($instance)
            {            //Method inherited from \Illuminate\Support\Facades\Facade
                \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade::swap($instance);
            }
        }
}

namespace AshAllenDesign\ShortURL\Facades {
        /**
         * @see Builder
         */
        class ShortURL
        {
            /**
             * Set the date and time that the short URL should
             * be activated and allowed to visit.
             *
             * @param  \AshAllenDesign\ShortURL\Classes\Carbon  $activationTime
             * @throws ShortURLException
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function activateAt($activationTime)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->activateAt($activationTime);
            }

            /**
             * Set the date and time that the short URL should
             * be deactivated and not allowed to visit.
             *
             * @param  \AshAllenDesign\ShortURL\Classes\Carbon  $deactivationTime
             * @throws ShortURLException
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function deactivateAt($deactivationTime)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->deactivateAt($deactivationTime);
            }

            /**
             * Set the destination URL that the shortened URL
             * will redirect to.
             *
             * @param  string                                   $url
             * @throws ShortURLException
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function destinationUrl($url)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->destinationUrl($url);
            }

            /**
             * Attempt to build a shortened URL and return it.
             *
             * @throws ShortURLException
             * @return \ShortURL
             * @static
             */
            public static function make()
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->make();
            }

            /**
             * Override the HTTP status code that will be used
             * for redirecting the visitor.
             *
             * @param  int                                      $statusCode
             * @throws ShortURLException
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function redirectStatusCode($statusCode)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->redirectStatusCode($statusCode);
            }

            /**
             * Reset the options for the class. This is useful
             * for stopping options carrying over into
             * different short URLs that are being
             * created with the same instance of
             * this class.
             *
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function resetOptions()
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->resetOptions();
            }

            /**
             * Set whether if the destination URL and shortened
             * URL should be forced to use HTTPS.
             *
             * @param  bool                                     $isSecure
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function secure($isSecure = true)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->secure($isSecure);
            }

            /**
             * Set whether if the shortened URL can be accessed
             * more than once.
             *
             * @param  bool                                     $isSingleUse
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function singleUse($isSingleUse = true)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->singleUse($isSingleUse);
            }

            /**
             * Set whether if the short URL should track the
             * browser of the visitor.
             *
             * @param  bool                                     $track
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function trackBrowser($track = true)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->trackBrowser($track);
            }

            /**
             * Set whether if the short URL should track the
             * browser version of the visitor.
             *
             * @param  bool                                     $track
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function trackBrowserVersion($track = true)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->trackBrowserVersion($track);
            }

            /**
             * Set whether if the short URL should track the
             * device type of the visitor.
             *
             * @param  bool                                     $track
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function trackDeviceType($track = true)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->trackDeviceType($track);
            }

            /**
             * Set whether if the short URL should track the
             * IP address of the visitor.
             *
             * @param  bool                                     $track
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function trackIPAddress($track = true)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->trackIPAddress($track);
            }

            /**
             * Set whether if the short URL should track the
             * operating system of the visitor.
             *
             * @param  bool                                     $track
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function trackOperatingSystem($track = true)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->trackOperatingSystem($track);
            }

            /**
             * Set whether if the short URL should track the
             * operating system version of the visitor.
             *
             * @param  bool                                     $track
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function trackOperatingSystemVersion($track = true)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->trackOperatingSystemVersion($track);
            }

            /**
             * Set whether if the short URL should track the
             * referer URL of the visitor.
             *
             * @param  bool                                     $track
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function trackRefererURL($track = true)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->trackRefererURL($track);
            }

            /**
             * Set whether if the short URL should track some
             * statistics of the visitors.
             *
             * @param  bool                                     $trackUrlVisits
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function trackVisits($trackUrlVisits = true)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->trackVisits($trackUrlVisits);
            }

            /**
             * Explicitly set a URL key for this short URL.
             *
             * @param  string                                   $key
             * @return \AshAllenDesign\ShortURL\Classes\Builder
             * @static
             */
            public static function urlKey($key)
            {
                // @var \AshAllenDesign\ShortURL\Classes\Builder $instance
                return $instance->urlKey($key);
            }
        }
}

namespace Michalisantoniou6\Cerberus {
        class CerberusFacade
        {
            /**
             * Check if the current user has a role or permission by its name.
             *
             * @param  array|string $roles       the role(s) needed
             * @param  array|string $permissions the permission(s) needed
             * @param  array        $options     the Options
             * @return bool
             * @static
             */
            public static function ability($roles, $permissions, $options = [])
            {
                // @var \Michalisantoniou6\Cerberus\Cerberus $instance
                return $instance->ability($roles, $permissions, $options);
            }

            /**
             * Check if the current user has a permission by its name.
             *
             * @param  string $permission permission string
             * @param  mixed  $requireAll
             * @return bool
             * @static
             */
            public static function hasPermission($permission, $requireAll = false)
            {
                // @var \Michalisantoniou6\Cerberus\Cerberus $instance
                return $instance->hasPermission($permission, $requireAll);
            }

            /**
             * Checks if the current user has a role by its name.
             *
             * @param  string $name       role name
             * @param  mixed  $role
             * @param  mixed  $requireAll
             * @return bool
             * @static
             */
            public static function hasRole($role, $requireAll = false)
            {
                // @var \Michalisantoniou6\Cerberus\Cerberus $instance
                return $instance->hasRole($role, $requireAll);
            }

            /**
             * Filters a route for a permission or set of permissions.
             *
             * If the third parameter is null then abort with status code 403.
             * Otherwise the $result is returned.
             *
             * @param  string       $route       Route pattern. i.e: "admin/*"
             * @param  array|string $permissions The permission(s) needed
             * @param  mixed        $result      i.e: Redirect::to('/')
             * @param  bool         $requireAll  User must have all permissions
             * @return mixed
             * @static
             */
            public static function routeNeedsPermission($route, $permissions, $result = null, $requireAll = true)
            {
                // @var \Michalisantoniou6\Cerberus\Cerberus $instance
                return $instance->routeNeedsPermission($route, $permissions, $result, $requireAll);
            }

            /**
             * Filters a route for a role or set of roles.
             *
             * If the third parameter is null then abort with status code 403.
             * Otherwise the $result is returned.
             *
             * @param  string       $route      Route pattern. i.e: "admin/*"
             * @param  array|string $roles      The role(s) needed
             * @param  mixed        $result     i.e: Redirect::to('/')
             * @param  bool         $requireAll User must have all roles
             * @return mixed
             * @static
             */
            public static function routeNeedsRole($route, $roles, $result = null, $requireAll = true)
            {
                // @var \Michalisantoniou6\Cerberus\Cerberus $instance
                return $instance->routeNeedsRole($route, $roles, $result, $requireAll);
            }

            /**
             * Filters a route for role(s) and/or permission(s).
             *
             * If the third parameter is null then abort with status code 403.
             * Otherwise the $result is returned.
             *
             * @param  string       $route       Route pattern. i.e: "admin/*"
             * @param  array|string $roles       The role(s) needed
             * @param  array|string $permissions The permission(s) needed
             * @param  mixed        $result      i.e: Redirect::to('/')
             * @param  bool         $requireAll  User must have all roles and permissions
             * @return void
             * @static
             */
            public static function routeNeedsRoleOrPermission($route, $roles, $permissions, $result = null, $requireAll = false)
            {
                // @var \Michalisantoniou6\Cerberus\Cerberus $instance
                $instance->routeNeedsRoleOrPermission($route, $roles, $permissions, $result, $requireAll);
            }

            /**
             * Get the currently authenticated user or null.
             *
             * @return \Michalisantoniou6\Cerberus\Illuminate\Auth\UserInterface|null
             * @static
             */
            public static function user()
            {
                // @var \Michalisantoniou6\Cerberus\Cerberus $instance
                return $instance->user();
            }
        }
}

namespace LynX39\LaraPdfMerger\Facades {
        class PdfMerger
        {
            /**
             * Add a PDF for inclusion in the merge with a valid file path. Pages should be formatted: 1,3,6, 12-16.
             *
             * @param $filepath
             * @param $pages
             * @param  mixed|null                      $orientation
             * @throws Exception
             * @return \LynX39\LaraPdfMerger\PdfManage
             * @static
             */
            public static function addPDF($filepath, $pages = 'all', $orientation = null)
            {
                // @var \LynX39\LaraPdfMerger\PdfManage $instance
                return $instance->addPDF($filepath, $pages, $orientation);
            }

            /**
             * Merges your provided PDFs and adds blank pages between documents as needed to allow duplex printing.
             *
             * @param  string     $orientation
             * @param  mixed      $meta
             * @throws \Exception if there are no PDFs to merge
             * @return void
             * @static
             */
            public static function duplexMerge($orientation = null, $meta = [])
            {
                // @var \LynX39\LaraPdfMerger\PdfManage $instance
                $instance->duplexMerge($orientation, $meta);
            }

            /**
             * @static
             */
            public static function init()
            {
                // @var \LynX39\LaraPdfMerger\PdfManage $instance
                return $instance->init();
            }

            /**
             * Merges your provided PDFs and outputs to specified location.
             *
             * @param  string     $orientation
             * @param  mixed      $meta
             * @throws \Exception if there are no PDFs to merge
             * @return void
             * @static
             */
            public static function merge($orientation = null, $meta = [])
            {
                // @var \LynX39\LaraPdfMerger\PdfManage $instance
                $instance->merge($orientation, $meta);
            }

            /**
             * @static
             * @param mixed $outputpath
             * @param mixed $outputmode
             */
            public static function save($outputpath = 'newfile.pdf', $outputmode = 'file')
            {
                // @var \LynX39\LaraPdfMerger\PdfManage $instance
                return $instance->save($outputpath, $outputmode);
            }
        }
}

namespace DarkGhostHunter\Laraload\Facades {
        class Laraload
        {
            /**
             * Registers a callback to use to append files to the Preloader.
             *
             * @param array|callable|string $append
             * @static
             */
            public static function append($append)
            {
                // @var \DarkGhostHunter\Laraload\Laraload $instance
                return $instance->append($append);
            }

            /**
             * Registers a callback to use to exclude files from the Preloader.
             *
             * @param array|callable|string $exclude
             * @static
             */
            public static function exclude($exclude)
            {
                // @var \DarkGhostHunter\Laraload\Laraload $instance
                return $instance->exclude($exclude);
            }

            /**
             * Generates the Preloader Script.
             *
             * @return bool
             * @static
             */
            public static function generate()
            {
                // @var \DarkGhostHunter\Laraload\Laraload $instance
                return $instance->generate();
            }
        }
}

namespace Facade\Ignition\Facades {
        /**
         * Class Flare.
         *
         * @see \Facade\FlareClient\Flare
         */
        class Flare
        {
            /**
             * @static
             */
            public static function anonymizeIp()
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->anonymizeIp();
            }

            /**
             * @static
             * @param mixed $applicationPath
             */
            public static function applicationPath($applicationPath)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->applicationPath($applicationPath);
            }

            /**
             * @static
             * @param mixed $key
             * @param mixed $value
             */
            public static function context($key, $value)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->context($key, $value);
            }

            /**
             * @static
             * @param mixed $throwable
             */
            public static function createReport($throwable)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->createReport($throwable);
            }

            /**
             * @static
             * @param mixed $message
             * @param mixed $logLevel
             */
            public static function createReportFromMessage($message, $logLevel)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->createReportFromMessage($message, $logLevel);
            }

            /**
             * @static
             * @param mixed $groupName
             * @param mixed $default
             */
            public static function getGroup($groupName = 'context', $default = [])
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->getGroup($groupName, $default);
            }

            /**
             * @static
             */
            public static function getMiddleware()
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->getMiddleware();
            }

            /**
             * @static
             */
            public static function getMiddlewares()
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->getMiddlewares();
            }

            /**
             * @static
             * @param mixed $name
             * @param mixed $messageLevel
             * @param mixed $metaData
             */
            public static function glow($name, $messageLevel = 'info', $metaData = [])
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->glow($name, $messageLevel, $metaData);
            }

            /**
             * @static
             * @param mixed $groupName
             * @param mixed $properties
             */
            public static function group($groupName, $properties)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->group($groupName, $properties);
            }

            /**
             * @static
             * @param mixed $code
             * @param mixed $message
             * @param mixed $file
             * @param mixed $line
             */
            public static function handleError($code, $message, $file = '', $line = 0)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->handleError($code, $message, $file, $line);
            }

            /**
             * @static
             * @param mixed $throwable
             */
            public static function handleException($throwable)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->handleException($throwable);
            }

            /**
             * @static
             * @param mixed $messageLevel
             */
            public static function messageLevel($messageLevel)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->messageLevel($messageLevel);
            }

            /**
             * @static
             * @param mixed      $apiKey
             * @param mixed|null $apiSecret
             * @param mixed|null $contextDetector
             * @param mixed|null $container
             */
            public static function register($apiKey, $apiSecret = null, $contextDetector = null, $container = null)
            {
                return \Facade\FlareClient\Flare::register($apiKey, $apiSecret, $contextDetector, $container);
            }

            /**
             * @static
             */
            public static function registerErrorHandler()
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->registerErrorHandler();
            }

            /**
             * @static
             */
            public static function registerExceptionHandler()
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->registerExceptionHandler();
            }

            /**
             * @static
             */
            public static function registerFlareHandlers()
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->registerFlareHandlers();
            }

            /**
             * @static
             * @param mixed $callable
             */
            public static function registerMiddleware($callable)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->registerMiddleware($callable);
            }

            /**
             * @static
             * @param mixed      $throwable
             * @param mixed|null $callback
             */
            public static function report($throwable, $callback = null)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->report($throwable, $callback);
            }

            /**
             * @static
             * @param mixed      $message
             * @param mixed      $logLevel
             * @param mixed|null $callback
             */
            public static function reportMessage($message, $logLevel, $callback = null)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->reportMessage($message, $logLevel, $callback);
            }

            /**
             * @static
             */
            public static function reset()
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->reset();
            }

            /**
             * @static
             * @param mixed $throwable
             */
            public static function sendTestReport($throwable)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->sendTestReport($throwable);
            }

            /**
             * @static
             * @param mixed $stage
             */
            public static function stage($stage)
            {
                // @var \Facade\FlareClient\Flare $instance
                return $instance->stage($stage);
            }
        }
}

namespace Intervention\Image\Facades {
        class Image
        {
            /**
             * Create new cached image and run callback
             * (requires additional package intervention/imagecache).
             *
             * @param  \Closure $callback
             * @param  int      $lifetime
             * @param  bool     $returnObj
             * @return \Image
             * @static
             */
            public static function cache($callback, $lifetime = null, $returnObj = false)
            {
                // @var \Intervention\Image\ImageManager $instance
                return $instance->cache($callback, $lifetime, $returnObj);
            }

            /**
             * Creates an empty image canvas.
             *
             * @param  int                       $width
             * @param  int                       $height
             * @param  mixed                     $background
             * @return \Intervention\Image\Image
             * @static
             */
            public static function canvas($width, $height, $background = null)
            {
                // @var \Intervention\Image\ImageManager $instance
                return $instance->canvas($width, $height, $background);
            }

            /**
             * Overrides configuration settings.
             *
             * @param  array $config
             * @return self
             * @static
             */
            public static function configure($config = [])
            {
                // @var \Intervention\Image\ImageManager $instance
                return $instance->configure($config);
            }

            /**
             * Initiates an Image instance from different input types.
             *
             * @param  mixed                     $data
             * @return \Intervention\Image\Image
             * @static
             */
            public static function make($data)
            {
                // @var \Intervention\Image\ImageManager $instance
                return $instance->make($data);
            }
        }
}

namespace Jenssegers\Agent\Facades {
        class Agent
        {
            /**
             * Get the browser name.
             *
             * @param  string|null $userAgent
             * @return bool|string
             * @static
             */
            public static function browser($userAgent = null)
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->browser($userAgent);
            }

            /**
             * Check the HTTP headers for signs of mobile.
             *
             * This is the fastest mobile check possible; it's used
             * inside isMobile() method.
             *
             * @return bool
             * @static
             */
            public static function checkHttpHeadersForMobile()
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->checkHttpHeadersForMobile();
            }

            /**
             * Get the device name.
             *
             * @param  string|null $userAgent
             * @return bool|string
             * @static
             */
            public static function device($userAgent = null)
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->device($userAgent);
            }

            /**
             * Get the device type.
             *
             * @param  null   $userAgent
             * @param  null   $httpHeaders
             * @return string
             * @static
             */
            public static function deviceType($userAgent = null, $httpHeaders = null)
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->deviceType($userAgent, $httpHeaders);
            }

            /**
             * @static
             */
            public static function getBrowsers()
            {
                return \Jenssegers\Agent\Agent::getBrowsers();
            }

            /**
             * Retrieves the cloudfront headers.
             *
             * @return array
             * @static
             */
            public static function getCfHeaders()
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getCfHeaders();
            }

            /**
             * @return \Jenssegers\Agent\CrawlerDetect
             * @static
             */
            public static function getCrawlerDetect()
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getCrawlerDetect();
            }

            /**
             * @static
             */
            public static function getDesktopDevices()
            {
                return \Jenssegers\Agent\Agent::getDesktopDevices();
            }

            /**
             * Get all detection rules. These rules include the additional
             * platforms and browsers and utilities.
             *
             * @return array
             * @static
             */
            public static function getDetectionRulesExtended()
            {
                return \Jenssegers\Agent\Agent::getDetectionRulesExtended();
            }

            /**
             * Retrieves a particular header. If it doesn't exist, no exception/error is caused.
             *
             * Simply null is returned.
             *
             * @param  string      $header The name of the header to retrieve. Can be HTTP compliant such as
             *                             "User-Agent" or "X-Device-User-Agent" or can be php-esque with the
             *                             all-caps, HTTP_ prefixed, underscore seperated awesomeness.
             * @return string|null the value of the header
             * @static
             */
            public static function getHttpHeader($header)
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getHttpHeader($header);
            }

            /**
             * Retrieves the HTTP headers.
             *
             * @return array
             * @static
             */
            public static function getHttpHeaders()
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getHttpHeaders();
            }

            /**
             * @static
             */
            public static function getMatchesArray()
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getMatchesArray();
            }

            /**
             * @static
             */
            public static function getMatchingRegex()
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getMatchingRegex();
            }

            /**
             * Method gets the mobile detection rules. This method is used for the magic methods $detect->is*().
             *
             * @deprecated since version 2.6.9
             * @return array all the rules (but not extended)
             * @static
             */
            public static function getMobileDetectionRules()
            {            //Method inherited from \Mobile_Detect
                return \Jenssegers\Agent\Agent::getMobileDetectionRules();
            }

            /**
             * Method gets the mobile detection rules + utilities.
             *
             * The reason this is separate is because utilities rules
             * don't necessary imply mobile. This method is used inside
             * the new $detect->is('stuff') method.
             *
             * @deprecated since version 2.6.9
             * @return array all the rules + extended
             * @static
             */
            public static function getMobileDetectionRulesExtended()
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getMobileDetectionRulesExtended();
            }

            /**
             * @static
             */
            public static function getMobileHeaders()
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getMobileHeaders();
            }

            /**
             * @static
             */
            public static function getOperatingSystems()
            {
                return \Jenssegers\Agent\Agent::getOperatingSystems();
            }

            /**
             * Retrieve the list of known phone devices.
             *
             * @return array list of phone devices
             * @static
             */
            public static function getPhoneDevices()
            {            //Method inherited from \Mobile_Detect
                return \Jenssegers\Agent\Agent::getPhoneDevices();
            }

            /**
             * @static
             */
            public static function getPlatforms()
            {
                return \Jenssegers\Agent\Agent::getPlatforms();
            }

            /**
             * @static
             */
            public static function getProperties()
            {
                return \Jenssegers\Agent\Agent::getProperties();
            }

            /**
             * @static
             */
            public static function getRules()
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getRules();
            }

            /**
             * Get the current script version.
             *
             * This is useful for the demo.php file,
             * so people can check on what version they are testing
             * for mobile devices.
             *
             * @return string the version number in semantic version format
             * @static
             */
            public static function getScriptVersion()
            {            //Method inherited from \Mobile_Detect
                return \Jenssegers\Agent\Agent::getScriptVersion();
            }

            /**
             * Retrieve the list of known tablet devices.
             *
             * @return array list of tablet devices
             * @static
             */
            public static function getTabletDevices()
            {            //Method inherited from \Mobile_Detect
                return \Jenssegers\Agent\Agent::getTabletDevices();
            }

            /**
             * Get all possible HTTP headers that
             * can contain the User-Agent string.
             *
             * @return array list of HTTP headers
             * @static
             */
            public static function getUaHttpHeaders()
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getUaHttpHeaders();
            }

            /**
             * Retrieve the User-Agent.
             *
             * @return string|null the user agent if it's set
             * @static
             */
            public static function getUserAgent()
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->getUserAgent();
            }

            /**
             * Alias for getBrowsers() method.
             *
             * @return array list of user agents
             * @static
             */
            public static function getUserAgents()
            {            //Method inherited from \Mobile_Detect
                return \Jenssegers\Agent\Agent::getUserAgents();
            }

            /**
             * Retrieve the list of known utilities.
             *
             * @return array list of utilities
             * @static
             */
            public static function getUtilities()
            {            //Method inherited from \Mobile_Detect
                return \Jenssegers\Agent\Agent::getUtilities();
            }

            /**
             * This method checks for a certain property in the
             * userAgent.
             *
             * @todo : The httpHeaders part is not yet used.
             * @param  string        $key
             * @param  string        $userAgent   deprecated
             * @param  string        $httpHeaders deprecated
             * @return bool|int|null
             * @static
             */
            public static function is($key, $userAgent = null, $httpHeaders = null)
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->is($key, $userAgent, $httpHeaders);
            }

            /**
             * Check if the device is a desktop computer.
             *
             * @param  string|null $userAgent   deprecated
             * @param  array       $httpHeaders deprecated
             * @return bool
             * @static
             */
            public static function isDesktop($userAgent = null, $httpHeaders = null)
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->isDesktop($userAgent, $httpHeaders);
            }

            /**
             * Check if the device is mobile.
             *
             * Returns true if any type of mobile device detected, including special ones
             *
             * @param  null $userAgent   deprecated
             * @param  null $httpHeaders deprecated
             * @return bool
             * @static
             */
            public static function isMobile($userAgent = null, $httpHeaders = null)
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->isMobile($userAgent, $httpHeaders);
            }

            /**
             * Check if the device is a mobile phone.
             *
             * @param  string|null $userAgent   deprecated
             * @param  array       $httpHeaders deprecated
             * @return bool
             * @static
             */
            public static function isPhone($userAgent = null, $httpHeaders = null)
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->isPhone($userAgent, $httpHeaders);
            }

            /**
             * Check if device is a robot.
             *
             * @param  string|null $userAgent
             * @return bool
             * @static
             */
            public static function isRobot($userAgent = null)
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->isRobot($userAgent);
            }

            /**
             * Check if the device is a tablet.
             *
             * Return true if any type of tablet device is detected.
             *
             * @param  string $userAgent   deprecated
             * @param  array  $httpHeaders deprecated
             * @return bool
             * @static
             */
            public static function isTablet($userAgent = null, $httpHeaders = null)
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->isTablet($userAgent, $httpHeaders);
            }

            /**
             * Get accept languages.
             *
             * @param  string $acceptLanguage
             * @return array
             * @static
             */
            public static function languages($acceptLanguage = null)
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->languages($acceptLanguage);
            }

            /**
             * Some detection rules are relative (not standard),
             * because of the diversity of devices, vendors and
             * their conventions in representing the User-Agent or
             * the HTTP headers.
             *
             * This method will be used to check custom regexes against
             * the User-Agent string.
             *
             * @param $regex
             * @param  string $userAgent
             * @return bool
             * @todo : search in the HTTP headers too.
             * @static
             */
            public static function match($regex, $userAgent = null)
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->match($regex, $userAgent);
            }

            /**
             * Retrieve the mobile grading, using self::MOBILE_GRADE_* constants.
             *
             * @return string one of the self::MOBILE_GRADE_* constants
             * @static
             */
            public static function mobileGrade()
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->mobileGrade();
            }

            /**
             * Get the platform name.
             *
             * @param  string|null $userAgent
             * @return bool|string
             * @static
             */
            public static function platform($userAgent = null)
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->platform($userAgent);
            }

            /**
             * Prepare the version number.
             *
             * @todo Remove the error supression from str_replace() call.
             * @param  string $ver The string version, like "2.6.21.2152";
             * @return float
             * @static
             */
            public static function prepareVersionNo($ver)
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->prepareVersionNo($ver);
            }

            /**
             * Get the robot name.
             *
             * @param  string|null $userAgent
             * @return bool|string
             * @static
             */
            public static function robot($userAgent = null)
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->robot($userAgent);
            }

            /**
             * Set CloudFront headers
             * http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/header-caching.html#header-caching-web-device.
             *
             * @param  array $cfHeaders List of HTTP headers
             * @return bool  If there were CloudFront headers to be set
             * @static
             */
            public static function setCfHeaders($cfHeaders = null)
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->setCfHeaders($cfHeaders);
            }

            /**
             * Set the detection type. Must be one of self::DETECTION_TYPE_MOBILE or
             * self::DETECTION_TYPE_EXTENDED. Otherwise, nothing is set.
             *
             * @deprecated since version 2.6.9
             * @param string $type The type. Must be a self::DETECTION_TYPE_* constant. The default
             *                     parameter is null which will default to self::DETECTION_TYPE_MOBILE.
             * @static
             */
            public static function setDetectionType($type = null)
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->setDetectionType($type);
            }

            /**
             * Set the HTTP Headers. Must be PHP-flavored. This method will reset existing headers.
             *
             * @param array $httpHeaders The headers to set. If null, then using PHP's _SERVER to extract
             *                           the headers. The default null is left for backwards compatibility.
             * @static
             */
            public static function setHttpHeaders($httpHeaders = null)
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->setHttpHeaders($httpHeaders);
            }

            /**
             * Set the User-Agent to be used.
             *
             * @param  string      $userAgent the user agent string to set
             * @return string|null
             * @static
             */
            public static function setUserAgent($userAgent = null)
            {            //Method inherited from \Mobile_Detect
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->setUserAgent($userAgent);
            }

            /**
             * @static
             * @param mixed $propertyName
             * @param mixed $type
             */
            public static function version($propertyName, $type = 'text')
            {
                // @var \Jenssegers\Agent\Agent $instance
                return $instance->version($propertyName, $type);
            }
        }
}

namespace jeremykenedy\Slack\Laravel {
        class Facade
        {
            /**
             * @static
             * @param mixed|null $callback
             */
            public static function assertMessageSent($callback = null)
            {
                // @var \jeremykenedy\Slack\Laravel\Fakes\SlackFake $instance
                return $instance->assertMessageSent($callback);
            }

            /**
             * @static
             * @param mixed      $channel
             * @param mixed|null $callback
             */
            public static function assertMessageSentTo($channel, $callback = null)
            {
                // @var \jeremykenedy\Slack\Laravel\Fakes\SlackFake $instance
                return $instance->assertMessageSentTo($channel, $callback);
            }

            /**
             * @static
             * @param mixed $callback
             */
            public static function assertTrue($callback)
            {
                // @var \jeremykenedy\Slack\Laravel\Fakes\SlackFake $instance
                return $instance->assertTrue($callback);
            }

            /**
             * Create a new message with defaults.
             *
             * @return \jeremykenedy\Slack\Message
             * @static
             */
            public static function createMessage()
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->createMessage();
            }

            /**
             * Get whether message text should be formatted with
             * Slack's Markdown-like language.
             *
             * @return bool
             * @static
             */
            public static function getAllowMarkdown()
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->getAllowMarkdown();
            }

            /**
             * Get the default channel messages will be created for.
             *
             * @return string
             * @static
             */
            public static function getDefaultChannel()
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->getDefaultChannel();
            }

            /**
             * Get the default icon messages will be created with.
             *
             * @return string
             * @static
             */
            public static function getDefaultIcon()
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->getDefaultIcon();
            }

            /**
             * Get the default username messages will be created for.
             *
             * @return string
             * @static
             */
            public static function getDefaultUsername()
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->getDefaultUsername();
            }

            /**
             * Get the Slack endpoint.
             *
             * @return string
             * @static
             */
            public static function getEndpoint()
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->getEndpoint();
            }

            /**
             * Get whether messages sent will have names (like @jeremykenedy)
             * will be converted into links.
             *
             * @return bool
             * @static
             */
            public static function getLinkNames()
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->getLinkNames();
            }

            /**
             * Get the attachment fields which should be formatted
             * in Slack's Markdown-like language.
             *
             * @return array
             * @static
             */
            public static function getMarkdownInAttachments()
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->getMarkdownInAttachments();
            }

            /**
             * Get whether text links should be unfurled.
             *
             * @return bool
             * @static
             */
            public static function getUnfurlLinks()
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->getUnfurlLinks();
            }

            /**
             * Get whether media links should be unfurled.
             *
             * @return bool
             * @static
             */
            public static function getUnfurlMedia()
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->getUnfurlMedia();
            }

            /**
             * Prepares the payload to be sent to the webhook.
             *
             * @param  \jeremykenedy\Slack\Message $message The message to send
             * @return array
             * @static
             */
            public static function preparePayload($message)
            {
                // @var \jeremykenedy\Slack\Client $instance
                return $instance->preparePayload($message);
            }

            /**
             * Send a message.
             *
             * @param  \jeremykenedy\Slack\Message $message
             * @return void
             * @static
             */
            public static function sendMessage($message)
            {
                // @var \jeremykenedy\Slack\Client $instance
                $instance->sendMessage($message);
            }

            /**
             * Set whether message text should be formatted with
             * Slack's Markdown-like language.
             *
             * @param  bool $value
             * @return void
             * @static
             */
            public static function setAllowMarkdown($value)
            {
                // @var \jeremykenedy\Slack\Client $instance
                $instance->setAllowMarkdown($value);
            }

            /**
             * Set the default channel messages will be created for.
             *
             * @param  string $channel
             * @return void
             * @static
             */
            public static function setDefaultChannel($channel)
            {
                // @var \jeremykenedy\Slack\Client $instance
                $instance->setDefaultChannel($channel);
            }

            /**
             * Set the default icon messages will be created with.
             *
             * @param  string $icon
             * @return void
             * @static
             */
            public static function setDefaultIcon($icon)
            {
                // @var \jeremykenedy\Slack\Client $instance
                $instance->setDefaultIcon($icon);
            }

            /**
             * Set the default username messages will be created for.
             *
             * @param  string $username
             * @return void
             * @static
             */
            public static function setDefaultUsername($username)
            {
                // @var \jeremykenedy\Slack\Client $instance
                $instance->setDefaultUsername($username);
            }

            /**
             * Set the Slack endpoint.
             *
             * @param  string $endpoint
             * @return void
             * @static
             */
            public static function setEndpoint($endpoint)
            {
                // @var \jeremykenedy\Slack\Client $instance
                $instance->setEndpoint($endpoint);
            }

            /**
             * Set whether messages sent will have names (like @jeremykenedy)
             * will be converted into links.
             *
             * @param  bool $value
             * @return void
             * @static
             */
            public static function setLinkNames($value)
            {
                // @var \jeremykenedy\Slack\Client $instance
                $instance->setLinkNames($value);
            }

            /**
             * Set the attachment fields which should be formatted
             * in Slack's Markdown-like language.
             *
             * @param  array $fields
             * @return void
             * @static
             */
            public static function setMarkdownInAttachments($fields)
            {
                // @var \jeremykenedy\Slack\Client $instance
                $instance->setMarkdownInAttachments($fields);
            }

            /**
             * Set whether text links should be unfurled.
             *
             * @param  bool $value
             * @return void
             * @static
             */
            public static function setUnfurlLinks($value)
            {
                // @var \jeremykenedy\Slack\Client $instance
                $instance->setUnfurlLinks($value);
            }

            /**
             * Set whether media links should be unfurled.
             *
             * @param  bool $value
             * @return void
             * @static
             */
            public static function setUnfurlMedia($value)
            {
                // @var \jeremykenedy\Slack\Client $instance
                $instance->setUnfurlMedia($value);
            }
        }
}

namespace Laravel\Nova {
        class Nova
        {
        }
}

namespace Collective\Html {
        /**
         * @see \Collective\Html\FormBuilder
         */
        class FormFacade
        {
            /**
             * Create a button element.
             *
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function button($value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->button($value, $options);
            }

            /**
             * Create a checkbox input field.
             *
             * @param  string                         $name
             * @param  mixed                          $value
             * @param  bool                           $checked
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function checkbox($name, $value = 1, $checked = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->checkbox($name, $value, $checked, $options);
            }

            /**
             * Close the current form.
             *
             * @return string
             * @static
             */
            public static function close()
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->close();
            }

            /**
             * Create a color input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function color($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->color($name, $value, $options);
            }

            /**
             * Register a custom component.
             *
             * @param $name
             * @param $view
             * @param  array $signature
             * @return void
             * @static
             */
            public static function component($name, $view, $signature)
            {
                \Collective\Html\FormBuilder::component($name, $view, $signature);
            }

            /**
             * Dynamically handle calls to the class.
             *
             * @param  string                                $method
             * @param  array                                 $parameters
             * @throws \BadMethodCallException
             * @return \Illuminate\Contracts\View\View|mixed
             * @static
             */
            public static function componentCall($method, $parameters)
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->componentCall($method, $parameters);
            }

            /**
             * Take Request in fill process.
             *
             * @param bool $consider
             * @static
             */
            public static function considerRequest($consider = true)
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->considerRequest($consider);
            }

            /**
             * Create a datalist box field.
             *
             * @param  string                         $id
             * @param  array                          $list
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function datalist($id, $list = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->datalist($id, $list);
            }

            /**
             * Create a date input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function date($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->date($name, $value, $options);
            }

            /**
             * Create a datetime input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function datetime($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->datetime($name, $value, $options);
            }

            /**
             * Create a datetime-local input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function datetimeLocal($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->datetimeLocal($name, $value, $options);
            }

            /**
             * Create an e-mail input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function email($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->email($name, $value, $options);
            }

            /**
             * Create a file input field.
             *
             * @param  string                         $name
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function file($name, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->file($name, $options);
            }

            /**
             * Get the ID attribute for a field name.
             *
             * @param  string $name
             * @param  array  $attributes
             * @return string
             * @static
             */
            public static function getIdAttribute($name, $attributes)
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->getIdAttribute($name, $attributes);
            }

            /**
             * Get the current model instance on the form builder.
             *
             * @return mixed $model
             * @static
             */
            public static function getModel()
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->getModel();
            }

            /**
             * Get the select option for the given value.
             *
             * @param  string                         $display
             * @param  string                         $value
             * @param  string                         $selected
             * @param  array                          $attributes
             * @param  array                          $optgroupAttributes
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function getSelectOption($display, $value, $selected, $attributes = [], $optgroupAttributes = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->getSelectOption($display, $value, $selected, $attributes, $optgroupAttributes);
            }

            /**
             * Get the session store implementation.
             *
             * @return \Illuminate\Contracts\Session\Session $session
             * @static
             */
            public static function getSessionStore()
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->getSessionStore();
            }

            /**
             * Get the value that should be assigned to the field.
             *
             * @param  string $name
             * @param  string $value
             * @return mixed
             * @static
             */
            public static function getValueAttribute($name, $value = null)
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->getValueAttribute($name, $value);
            }

            /**
             * Check if a component is registered.
             *
             * @param $name
             * @return bool
             * @static
             */
            public static function hasComponent($name)
            {
                return \Collective\Html\FormBuilder::hasComponent($name);
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Collective\Html\FormBuilder::hasMacro($name);
            }

            /**
             * Create a hidden input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function hidden($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->hidden($name, $value, $options);
            }

            /**
             * Create a HTML image input element.
             *
             * @param  string                         $url
             * @param  string                         $name
             * @param  array                          $attributes
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function image($url, $name = null, $attributes = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->image($url, $name, $attributes);
            }

            /**
             * Create a form input field.
             *
             * @param  string                         $type
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function input($type, $name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->input($type, $name, $value, $options);
            }

            /**
             * Create a form label element.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @param  bool                           $escape_html
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function label($name, $value = null, $options = [], $escape_html = true)
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->label($name, $value, $options, $escape_html);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Collective\Html\FormBuilder::macro($name, $macro);
            }

            /**
             * Dynamically handle calls to the class.
             *
             * @param  string                  $method
             * @param  array                   $parameters
             * @throws \BadMethodCallException
             * @return mixed
             * @static
             */
            public static function macroCall($method, $parameters)
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->macroCall($method, $parameters);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Collective\Html\FormBuilder::mixin($mixin, $replace);
            }

            /**
             * Create a new model based form builder.
             *
             * @param  mixed                          $model
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function model($model, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->model($model, $options);
            }

            /**
             * Create a month input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function month($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->month($name, $value, $options);
            }

            /**
             * Create a number input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function number($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->number($name, $value, $options);
            }

            /**
             * Get a value from the session's old input.
             *
             * @param  string $name
             * @return mixed
             * @static
             */
            public static function old($name)
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->old($name);
            }

            /**
             * Determine if the old input is empty.
             *
             * @return bool
             * @static
             */
            public static function oldInputIsEmpty()
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->oldInputIsEmpty();
            }

            /**
             * Open up a new HTML form.
             *
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function open($options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->open($options);
            }

            /**
             * Create a password input field.
             *
             * @param  string                         $name
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function password($name, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->password($name, $options);
            }

            /**
             * Create a radio button input field.
             *
             * @param  string                         $name
             * @param  mixed                          $value
             * @param  bool                           $checked
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function radio($name, $value = null, $checked = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->radio($name, $value, $checked, $options);
            }

            /**
             * Create a range input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function range($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->range($name, $value, $options);
            }

            /**
             * Create a HTML reset input element.
             *
             * @param  string                         $value
             * @param  array                          $attributes
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function reset($value, $attributes = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->reset($value, $attributes);
            }

            /**
             * Create a search input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function search($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->search($name, $value, $options);
            }

            /**
             * Create a select box field.
             *
             * @param  string                         $name
             * @param  array                          $list
             * @param  bool|string                    $selected
             * @param  array                          $selectAttributes
             * @param  array                          $optionsAttributes
             * @param  array                          $optgroupsAttributes
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function select($name, $list = [], $selected = null, $selectAttributes = [], $optionsAttributes = [], $optgroupsAttributes = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->select($name, $list, $selected, $selectAttributes, $optionsAttributes, $optgroupsAttributes);
            }

            /**
             * Create a select month field.
             *
             * @param  string                         $name
             * @param  string                         $selected
             * @param  array                          $options
             * @param  string                         $format
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function selectMonth($name, $selected = null, $options = [], $format = '%B')
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->selectMonth($name, $selected, $options, $format);
            }

            /**
             * Create a select range field.
             *
             * @param  string                         $name
             * @param  string                         $begin
             * @param  string                         $end
             * @param  string                         $selected
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function selectRange($name, $begin, $end, $selected = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->selectRange($name, $begin, $end, $selected, $options);
            }

            /**
             * Create a select year field.
             *
             * @param  string $name
             * @param  string $begin
             * @param  string $end
             * @param  string $selected
             * @param  array  $options
             * @return mixed
             * @static
             */
            public static function selectYear()
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->selectYear();
            }

            /**
             * Set the model instance on the form builder.
             *
             * @param  mixed $model
             * @return void
             * @static
             */
            public static function setModel($model)
            {
                // @var \Collective\Html\FormBuilder $instance
                $instance->setModel($model);
            }

            /**
             * Set the session store implementation.
             *
             * @param  \Illuminate\Contracts\Session\Session $session
             * @return \Collective\Html\FormBuilder
             * @static
             */
            public static function setSessionStore($session)
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->setSessionStore($session);
            }

            /**
             * Create a submit button element.
             *
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function submit($value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->submit($value, $options);
            }

            /**
             * Create a tel input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function tel($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->tel($name, $value, $options);
            }

            /**
             * Create a text input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function text($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->text($name, $value, $options);
            }

            /**
             * Create a textarea input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function textarea($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->textarea($name, $value, $options);
            }

            /**
             * Create a time input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function time($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->time($name, $value, $options);
            }

            /**
             * Generate a hidden field with the current CSRF token.
             *
             * @return string
             * @static
             */
            public static function token()
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->token();
            }

            /**
             * Create a url input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function url($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->url($name, $value, $options);
            }

            /**
             * Create a week input field.
             *
             * @param  string                         $name
             * @param  string                         $value
             * @param  array                          $options
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function week($name, $value = null, $options = [])
            {
                // @var \Collective\Html\FormBuilder $instance
                return $instance->week($name, $value, $options);
            }
        }
        /**
         * @see \Collective\Html\HtmlBuilder
         */
        class HtmlFacade
        {
            /**
             * Build an HTML attribute string from an array.
             *
             * @param  array  $attributes
             * @return string
             * @static
             */
            public static function attributes($attributes)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->attributes($attributes);
            }

            /**
             * Register a custom component.
             *
             * @param $name
             * @param $view
             * @param  array $signature
             * @return void
             * @static
             */
            public static function component($name, $view, $signature)
            {
                \Collective\Html\HtmlBuilder::component($name, $view, $signature);
            }

            /**
             * Dynamically handle calls to the class.
             *
             * @param  string                                $method
             * @param  array                                 $parameters
             * @throws \BadMethodCallException
             * @return \Illuminate\Contracts\View\View|mixed
             * @static
             */
            public static function componentCall($method, $parameters)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->componentCall($method, $parameters);
            }

            /**
             * Convert entities to HTML characters.
             *
             * @param  string $value
             * @return string
             * @static
             */
            public static function decode($value)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->decode($value);
            }

            /**
             * Generate a description list of items.
             *
             * @param  array                          $list
             * @param  array                          $attributes
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function dl($list, $attributes = [])
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->dl($list, $attributes);
            }

            /**
             * Obfuscate an e-mail address to prevent spam-bots from sniffing it.
             *
             * @param  string $email
             * @return string
             * @static
             */
            public static function email($email)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->email($email);
            }

            /**
             * Convert an HTML string to entities.
             *
             * @param  string $value
             * @return string
             * @static
             */
            public static function entities($value)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->entities($value);
            }

            /**
             * Generate a link to a Favicon file.
             *
             * @param  string                         $url
             * @param  array                          $attributes
             * @param  bool                           $secure
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function favicon($url, $attributes = [], $secure = null)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->favicon($url, $attributes, $secure);
            }

            /**
             * Check if a component is registered.
             *
             * @param $name
             * @return bool
             * @static
             */
            public static function hasComponent($name)
            {
                return \Collective\Html\HtmlBuilder::hasComponent($name);
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Collective\Html\HtmlBuilder::hasMacro($name);
            }

            /**
             * Generate an HTML image element.
             *
             * @param  string                         $url
             * @param  string                         $alt
             * @param  array                          $attributes
             * @param  bool                           $secure
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function image($url, $alt = null, $attributes = [], $secure = null)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->image($url, $alt, $attributes, $secure);
            }

            /**
             * Generate a HTML link.
             *
             * @param  string                         $url
             * @param  string                         $title
             * @param  array                          $attributes
             * @param  bool                           $secure
             * @param  bool                           $escape
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function link($url, $title = null, $attributes = [], $secure = null, $escape = true)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->link($url, $title, $attributes, $secure, $escape);
            }

            /**
             * Generate a HTML link to a controller action.
             *
             * @param  string                         $action
             * @param  string                         $title
             * @param  array                          $parameters
             * @param  array                          $attributes
             * @param  bool                           $secure
             * @param  bool                           $escape
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function linkAction($action, $title = null, $parameters = [], $attributes = [], $secure = null, $escape = true)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->linkAction($action, $title, $parameters, $attributes, $secure, $escape);
            }

            /**
             * Generate a HTML link to an asset.
             *
             * @param  string                         $url
             * @param  string                         $title
             * @param  array                          $attributes
             * @param  bool                           $secure
             * @param  bool                           $escape
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function linkAsset($url, $title = null, $attributes = [], $secure = null, $escape = true)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->linkAsset($url, $title, $attributes, $secure, $escape);
            }

            /**
             * Generate a HTML link to a named route.
             *
             * @param  string                         $name
             * @param  string                         $title
             * @param  array                          $parameters
             * @param  array                          $attributes
             * @param  bool                           $secure
             * @param  bool                           $escape
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function linkRoute($name, $title = null, $parameters = [], $attributes = [], $secure = null, $escape = true)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->linkRoute($name, $title, $parameters, $attributes, $secure, $escape);
            }

            /**
             * Generate a HTTPS HTML link to an asset.
             *
             * @param  string                         $url
             * @param  string                         $title
             * @param  array                          $attributes
             * @param  bool                           $escape
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function linkSecureAsset($url, $title = null, $attributes = [], $escape = true)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->linkSecureAsset($url, $title, $attributes, $escape);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Collective\Html\HtmlBuilder::macro($name, $macro);
            }

            /**
             * Dynamically handle calls to the class.
             *
             * @param  string                  $method
             * @param  array                   $parameters
             * @throws \BadMethodCallException
             * @return mixed
             * @static
             */
            public static function macroCall($method, $parameters)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->macroCall($method, $parameters);
            }

            /**
             * Generate a HTML link to an email address.
             *
             * @param  string                         $email
             * @param  string                         $title
             * @param  array                          $attributes
             * @param  bool                           $escape
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function mailto($email, $title = null, $attributes = [], $escape = true)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->mailto($email, $title, $attributes, $escape);
            }

            /**
             * Generate a meta tag.
             *
             * @param  string                         $name
             * @param  string                         $content
             * @param  array                          $attributes
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function meta($name, $content, $attributes = [])
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->meta($name, $content, $attributes);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Collective\Html\HtmlBuilder::mixin($mixin, $replace);
            }

            /**
             * Generates non-breaking space entities based on number supplied.
             *
             * @param  int    $num
             * @return string
             * @static
             */
            public static function nbsp($num = 1)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->nbsp($num);
            }

            /**
             * Obfuscate a string to prevent spam-bots from sniffing it.
             *
             * @param  string $value
             * @return string
             * @static
             */
            public static function obfuscate($value)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->obfuscate($value);
            }

            /**
             * Generate an ordered list of items.
             *
             * @param  array                                 $list
             * @param  array                                 $attributes
             * @return \Illuminate\Support\HtmlString|string
             * @static
             */
            public static function ol($list, $attributes = [])
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->ol($list, $attributes);
            }

            /**
             * Generate a link to a JavaScript file.
             *
             * @param  string                         $url
             * @param  array                          $attributes
             * @param  bool                           $secure
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function script($url, $attributes = [], $secure = null)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->script($url, $attributes, $secure);
            }

            /**
             * Generate a HTTPS HTML link.
             *
             * @param  string                         $url
             * @param  string                         $title
             * @param  array                          $attributes
             * @param  bool                           $escape
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function secureLink($url, $title = null, $attributes = [], $escape = true)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->secureLink($url, $title, $attributes, $escape);
            }

            /**
             * Generate a link to a CSS file.
             *
             * @param  string                         $url
             * @param  array                          $attributes
             * @param  bool                           $secure
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function style($url, $attributes = [], $secure = null)
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->style($url, $attributes, $secure);
            }

            /**
             * Generate an html tag.
             *
             * @param  string                         $tag
             * @param  mixed                          $content
             * @param  array                          $attributes
             * @return \Illuminate\Support\HtmlString
             * @static
             */
            public static function tag($tag, $content, $attributes = [])
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->tag($tag, $content, $attributes);
            }

            /**
             * Generate an un-ordered list of items.
             *
             * @param  array                                 $list
             * @param  array                                 $attributes
             * @return \Illuminate\Support\HtmlString|string
             * @static
             */
            public static function ul($list, $attributes = [])
            {
                // @var \Collective\Html\HtmlBuilder $instance
                return $instance->ul($list, $attributes);
            }
        }
}

namespace Maatwebsite\Excel\Facades {
        class Excel
        {
            /**
             * @param string        $fileName
             * @param callable|null $callback
             * @static
             */
            public static function assertDownloaded($fileName, $callback = null)
            {
                // @var \Maatwebsite\Excel\Fakes\ExcelFake $instance
                return $instance->assertDownloaded($fileName, $callback);
            }

            /**
             * @param string               $filePath
             * @param callable|string|null $disk
             * @param callable|null        $callback
             * @static
             */
            public static function assertImported($filePath, $disk = null, $callback = null)
            {
                // @var \Maatwebsite\Excel\Fakes\ExcelFake $instance
                return $instance->assertImported($filePath, $disk, $callback);
            }

            /**
             * @param string               $filePath
             * @param callable|string|null $disk
             * @param callable|null        $callback
             * @static
             */
            public static function assertQueued($filePath, $disk = null, $callback = null)
            {
                // @var \Maatwebsite\Excel\Fakes\ExcelFake $instance
                return $instance->assertQueued($filePath, $disk, $callback);
            }

            /**
             * @static
             * @param mixed $chain
             */
            public static function assertQueuedWithChain($chain)
            {
                // @var \Maatwebsite\Excel\Fakes\ExcelFake $instance
                return $instance->assertQueuedWithChain($chain);
            }

            /**
             * @param string               $filePath
             * @param callable|string|null $disk
             * @param callable|null        $callback
             * @static
             */
            public static function assertStored($filePath, $disk = null, $callback = null)
            {
                // @var \Maatwebsite\Excel\Fakes\ExcelFake $instance
                return $instance->assertStored($filePath, $disk, $callback);
            }

            /**
             * When asserting downloaded, stored, queued or imported, use regular string
             * comparison for matching file path.
             *
             * @return void
             * @static
             */
            public static function doNotMatchByRegex()
            {
                // @var \Maatwebsite\Excel\Fakes\ExcelFake $instance
                $instance->doNotMatchByRegex();
            }

            /**
             * @param  object                                               $export
             * @param  string|null                                          $fileName
             * @param  string                                               $writerType
             * @param  array                                                $headers
             * @throws \PhpOffice\PhpSpreadsheet\Exception
             * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
             * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
             * @static
             */
            public static function download($export, $fileName, $writerType = null, $headers = [])
            {
                // @var \Maatwebsite\Excel\Excel $instance
                return $instance->download($export, $fileName, $writerType, $headers);
            }

            /**
             * @param string   $concern
             * @param callable $handler
             * @param string   $event
             * @static
             */
            public static function extend($concern, $handler, $event = 'Maatwebsite\\Excel\\Events\\BeforeWriting')
            {
                return \Maatwebsite\Excel\Excel::extend($concern, $handler, $event);
            }

            /**
             * @param  object                                                               $import
             * @param  string|\Symfony\Component\HttpFoundation\File\UploadedFile           $filePath
             * @param  string|null                                                          $disk
             * @param  string|null                                                          $readerType
             * @return \Illuminate\Foundation\Bus\PendingDispatch|\Maatwebsite\Excel\Reader
             * @static
             */
            public static function import($import, $filePath, $disk = null, $readerType = null)
            {
                // @var \Maatwebsite\Excel\Excel $instance
                return $instance->import($import, $filePath, $disk, $readerType);
            }

            /**
             * When asserting downloaded, stored, queued or imported, use regular expression
             * to look for a matching file path.
             *
             * @return void
             * @static
             */
            public static function matchByRegex()
            {
                // @var \Maatwebsite\Excel\Fakes\ExcelFake $instance
                $instance->matchByRegex();
            }

            /**
             * @param  object                                     $export
             * @param  string                                     $filePath
             * @param  string|null                                $disk
             * @param  string                                     $writerType
             * @param  mixed                                      $diskOptions
             * @return \Illuminate\Foundation\Bus\PendingDispatch
             * @static
             */
            public static function queue($export, $filePath, $disk = null, $writerType = null, $diskOptions = [])
            {
                // @var \Maatwebsite\Excel\Excel $instance
                return $instance->queue($export, $filePath, $disk, $writerType, $diskOptions);
            }

            /**
             * @param  \Maatwebsite\Excel\ShouldQueue                             $import
             * @param  string|\Symfony\Component\HttpFoundation\File\UploadedFile $filePath
             * @param  string|null                                                $disk
             * @param  string                                                     $readerType
             * @return \Illuminate\Foundation\Bus\PendingDispatch
             * @static
             */
            public static function queueImport($import, $filePath, $disk = null, $readerType = null)
            {
                // @var \Maatwebsite\Excel\Excel $instance
                return $instance->queueImport($import, $filePath, $disk, $readerType);
            }

            /**
             * @param  object $export
             * @param  string $writerType
             * @return string
             * @static
             */
            public static function raw($export, $writerType)
            {
                // @var \Maatwebsite\Excel\Excel $instance
                return $instance->raw($export, $writerType);
            }

            /**
             * @param  object                                     $export
             * @param  string                                     $filePath
             * @param  string|null                                $disk
             * @param  string                                     $writerType
             * @param  mixed                                      $diskOptions
             * @param  mixed|null                                 $diskName
             * @throws \PhpOffice\PhpSpreadsheet\Exception
             * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
             * @return bool
             * @static
             */
            public static function store($export, $filePath, $diskName = null, $writerType = null, $diskOptions = [])
            {
                // @var \Maatwebsite\Excel\Excel $instance
                return $instance->store($export, $filePath, $diskName, $writerType, $diskOptions);
            }

            /**
             * @param  object                                                     $import
             * @param  string|\Symfony\Component\HttpFoundation\File\UploadedFile $filePath
             * @param  string|null                                                $disk
             * @param  string|null                                                $readerType
             * @return array
             * @static
             */
            public static function toArray($import, $filePath, $disk = null, $readerType = null)
            {
                // @var \Maatwebsite\Excel\Excel $instance
                return $instance->toArray($import, $filePath, $disk, $readerType);
            }

            /**
             * @param  object                                                     $import
             * @param  string|\Symfony\Component\HttpFoundation\File\UploadedFile $filePath
             * @param  string|null                                                $disk
             * @param  string|null                                                $readerType
             * @return \Maatwebsite\Excel\Collection
             * @static
             */
            public static function toCollection($import, $filePath, $disk = null, $readerType = null)
            {
                // @var \Maatwebsite\Excel\Excel $instance
                return $instance->toCollection($import, $filePath, $disk, $readerType);
            }
        }
}

namespace Mpbarlow\LaravelQueueDebouncer\Facade {
        /**
         * @see \Mpbarlow\LaravelQueueDebouncer\Debouncer
         */
        class Debouncer
        {
            /**
             * Indicate that the job should be dispatched after all database transactions have committed.
             *
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function afterCommit()
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->afterCommit();
            }

            /**
             * Set the desired connection for the chain.
             *
             * @param  string|null                               $connection
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function allOnConnection($connection)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->allOnConnection($connection);
            }

            /**
             * Set the desired queue for the chain.
             *
             * @param  string|null                               $queue
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function allOnQueue($queue)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->allOnQueue($queue);
            }

            /**
             * Indicate that the job should not wait until database transactions have been committed before dispatching.
             *
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function beforeCommit()
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->beforeCommit();
            }

            /**
             * Set the jobs that should run if this job is successful.
             *
             * @param  array                                     $chain
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function chain($chain)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->chain($chain);
            }

            /**
             * @param  \Closure|\Illuminate\Foundation\Bus\PendingChain|\Mpbarlow\LaravelQueueDebouncer\Dispatchable $job
             * @param  \DateInterval|\DateTimeInterface|int|null                                                     $wait
             * @return \Illuminate\Foundation\Bus\PendingDispatch
             * @static
             */
            public static function debounce($job, $wait)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->debounce($job, $wait);
            }

            /**
             * Set the desired delay for the job.
             *
             * @param  \DateInterval|\DateTimeInterface|int|null $delay
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function delay($delay)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->delay($delay);
            }

            /**
             * Dispatch the job with the given arguments.
             *
             * @return \Illuminate\Foundation\Bus\PendingDispatch
             * @static
             */
            public static function dispatch()
            {
                return \Mpbarlow\LaravelQueueDebouncer\Debouncer::dispatch();
            }

            /**
             * Dispatch a command to its appropriate handler after the current process.
             *
             * @return mixed
             * @static
             */
            public static function dispatchAfterResponse()
            {
                return \Mpbarlow\LaravelQueueDebouncer\Debouncer::dispatchAfterResponse();
            }

            /**
             * Dispatch the job with the given arguments if the given truth test passes.
             *
             * @param  bool                                                                  $boolean
             * @param  mixed                                                                 $arguments
             * @return \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent
             * @static
             */
            public static function dispatchIf($boolean, ...$arguments)
            {
                return \Mpbarlow\LaravelQueueDebouncer\Debouncer::dispatchIf($boolean, ...$arguments);
            }

            /**
             * Dispatch the next job on the chain.
             *
             * @return void
             * @static
             */
            public static function dispatchNextJobInChain()
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                $instance->dispatchNextJobInChain();
            }

            /**
             * Dispatch a command to its appropriate handler in the current process.
             *
             * @return mixed
             * @static
             */
            public static function dispatchNow()
            {
                return \Mpbarlow\LaravelQueueDebouncer\Debouncer::dispatchNow();
            }

            /**
             * Dispatch a command to its appropriate handler in the current process.
             *
             * Queuable jobs will be dispatched to the "sync" queue.
             *
             * @return mixed
             * @static
             */
            public static function dispatchSync()
            {
                return \Mpbarlow\LaravelQueueDebouncer\Debouncer::dispatchSync();
            }

            /**
             * Dispatch the job with the given arguments unless the given truth test passes.
             *
             * @param  bool                                                                  $boolean
             * @param  mixed                                                                 $arguments
             * @return \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent
             * @static
             */
            public static function dispatchUnless($boolean, ...$arguments)
            {
                return \Mpbarlow\LaravelQueueDebouncer\Debouncer::dispatchUnless($boolean, ...$arguments);
            }

            /**
             * Invoke all of the chain's failed job callbacks.
             *
             * @param  \Throwable $e
             * @return void
             * @static
             */
            public static function invokeChainCatchCallbacks($e)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                $instance->invokeChainCatchCallbacks($e);
            }

            /**
             * Set the desired connection for the job.
             *
             * @param  string|null                               $connection
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function onConnection($connection)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->onConnection($connection);
            }

            /**
             * Set the desired queue for the job.
             *
             * @param  string|null                               $queue
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function onQueue($queue)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->onQueue($queue);
            }

            /**
             * Specify the middleware the job should be dispatched through.
             *
             * @param  array|object                              $middleware
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function through($middleware)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->through($middleware);
            }

            /**
             * @param  \Closure|\Mpbarlow\LaravelQueueDebouncer\CacheKeyProvider $provider
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function usingCacheKeyProvider($provider)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->usingCacheKeyProvider($provider);
            }

            /**
             * @param  \Closure|\Mpbarlow\LaravelQueueDebouncer\UniqueIdentifierProvider $provider
             * @return \Mpbarlow\LaravelQueueDebouncer\Debouncer
             * @static
             */
            public static function usingUniqueIdentifierProvider($provider)
            {
                // @var \Mpbarlow\LaravelQueueDebouncer\Debouncer $instance
                return $instance->usingUniqueIdentifierProvider($provider);
            }

            /**
             * Set the jobs that should run if this job is successful.
             *
             * @param  array                                   $chain
             * @return \Illuminate\Foundation\Bus\PendingChain
             * @static
             */
            public static function withChain($chain)
            {
                return \Mpbarlow\LaravelQueueDebouncer\Debouncer::withChain($chain);
            }
        }
}

namespace Scoutapm\Laravel\Facades {
        /**
         * @see \Scoutapm\ScoutApmAgent
         */
        class ScoutApm
        {
            /**
             * @static
             * @param mixed $tag
             * @param mixed $value
             */
            public static function addContext($tag, $value)
            {
                // @var \Scoutapm\Agent $instance
                return $instance->addContext($tag, $value);
            }

            /**
             * @static
             * @param mixed $name
             * @param mixed $block
             */
            public static function backgroundTransaction($name, $block)
            {
                // @var \Scoutapm\Agent $instance
                return $instance->backgroundTransaction($name, $block);
            }

            /**
             * @static
             * @param mixed $newRequestUri
             */
            public static function changeRequestUri($newRequestUri)
            {
                // @var \Scoutapm\Agent $instance
                return $instance->changeRequestUri($newRequestUri);
            }

            /**
             * @static
             */
            public static function connect()
            {
                // @var \Scoutapm\Agent $instance
                return $instance->connect();
            }

            /**
             * @static
             */
            public static function enabled()
            {
                // @var \Scoutapm\Agent $instance
                return $instance->enabled();
            }

            /**
             * @static
             * @param mixed      $config
             * @param mixed      $logger
             * @param mixed|null $cache
             * @param mixed|null $connector
             * @param mixed|null $extentionCapabilities
             * @param mixed|null $locateFileOrFolder
             */
            public static function fromConfig($config, $logger, $cache = null, $connector = null, $extentionCapabilities = null, $locateFileOrFolder = null)
            {
                return \Scoutapm\Agent::fromConfig($config, $logger, $cache, $connector, $extentionCapabilities, $locateFileOrFolder);
            }

            /**
             * @internal
             * @deprecated
             * @static
             */
            public static function getRequest()
            {
                // @var \Scoutapm\Agent $instance
                return $instance->getRequest();
            }

            /**
             * @static
             */
            public static function ignore()
            {
                // @var \Scoutapm\Agent $instance
                return $instance->ignore();
            }

            /**
             * @static
             * @param mixed $path
             */
            public static function ignored($path)
            {
                // @var \Scoutapm\Agent $instance
                return $instance->ignored($path);
            }

            /**
             * @static
             * @param mixed $type
             * @param mixed $name
             * @param mixed $block
             */
            public static function instrument($type, $name, $block)
            {
                // @var \Scoutapm\Agent $instance
                return $instance->instrument($type, $name, $block);
            }

            /**
             * @static
             */
            public static function send()
            {
                // @var \Scoutapm\Agent $instance
                return $instance->send();
            }

            /**
             * @static
             * @param mixed $functionality
             */
            public static function shouldInstrument($functionality)
            {
                // @var \Scoutapm\Agent $instance
                return $instance->shouldInstrument($functionality);
            }

            /**
             * @static
             */
            public static function startNewRequest()
            {
                // @var \Scoutapm\Agent $instance
                return $instance->startNewRequest();
            }

            /**
             * @static
             * @param mixed      $operation
             * @param mixed|null $overrideTimestamp
             */
            public static function startSpan($operation, $overrideTimestamp = null)
            {
                // @var \Scoutapm\Agent $instance
                return $instance->startSpan($operation, $overrideTimestamp);
            }

            /**
             * @static
             */
            public static function stopSpan()
            {
                // @var \Scoutapm\Agent $instance
                return $instance->stopSpan();
            }

            /**
             * @static
             * @param mixed $tag
             * @param mixed $value
             */
            public static function tagRequest($tag, $value)
            {
                // @var \Scoutapm\Agent $instance
                return $instance->tagRequest($tag, $value);
            }

            /**
             * @static
             * @param mixed $name
             * @param mixed $block
             */
            public static function webTransaction($name, $block)
            {
                // @var \Scoutapm\Agent $instance
                return $instance->webTransaction($name, $block);
            }
        }
}

namespace Sentry\Laravel {
        class Facade
        {
            /**
             * Records a new breadcrumb which will be attached to future events. They
             * will be added to subsequent events to provide more context on user's
             * actions prior to an error or crash.
             *
             * @param  \Sentry\State\Breadcrumb $breadcrumb The breadcrumb to record
             * @return bool                     Whether the breadcrumb was actually added to the current scope
             * @static
             */
            public static function addBreadcrumb($breadcrumb)
            {
                // @var \Sentry\State\Hub $instance
                return $instance->addBreadcrumb($breadcrumb);
            }

            /**
             * Binds the given client to the current scope.
             *
             * @param \Sentry\State\ClientInterface $client The client
             * @static
             */
            public static function bindClient($client)
            {
                // @var \Sentry\State\Hub $instance
                return $instance->bindClient($client);
            }

            /**
             * Captures a new event using the provided data.
             *
             * @param \Event                       $event The event being captured
             * @param \Sentry\State\EventHint|null $hint  May contain additional information about the event
             * @static
             */
            public static function captureEvent($event, $hint = null)
            {
                // @var \Sentry\State\Hub $instance
                return $instance->captureEvent($event, $hint);
            }

            /**
             * Captures an exception event and sends it to Sentry.
             *
             * @param \Throwable                   $exception The exception
             * @param \Sentry\State\EventHint|null $hint      Object that can contain additional information about the event
             * @static
             */
            public static function captureException($exception, $hint = null)
            {
                // @var \Sentry\State\Hub $instance
                return $instance->captureException($exception, $hint);
            }

            /**
             * Captures an event that logs the last occurred error.
             *
             * @param \Sentry\State\EventHint|null $hint Object that can contain additional information about the event
             * @static
             */
            public static function captureLastError($hint = null)
            {
                // @var \Sentry\State\Hub $instance
                return $instance->captureLastError($hint);
            }

            /**
             * Captures a message event and sends it to Sentry.
             *
             * @param string                       $message The message
             * @param \Sentry\State\Severity|null  $level   The severity level of the message
             * @param \Sentry\State\EventHint|null $hint    Object that can contain additional information about the event
             * @static
             */
            public static function captureMessage($message, $level = null, $hint = null)
            {
                // @var \Sentry\State\Hub $instance
                return $instance->captureMessage($message, $level, $hint);
            }

            /**
             * Calls the given callback passing to it the current scope so that any
             * operation can be run within its context.
             *
             * @param callable $callback The callback to be executed
             * @static
             */
            public static function configureScope($callback)
            {
                // @var \Sentry\State\Hub $instance
                return $instance->configureScope($callback);
            }

            /**
             * Gets the client bound to the top of the stack.
             *
             * @static
             */
            public static function getClient()
            {
                // @var \Sentry\State\Hub $instance
                return $instance->getClient();
            }

            /**
             * Gets the integration whose FQCN matches the given one if it's available on the current client.
             *
             * @param string $className The FQCN of the integration
             * @psalm-template T of IntegrationInterface
             * @psalm-param class-string<T> $className
             * @psalm-return T|null
             * @static
             */
            public static function getIntegration($className)
            {
                // @var \Sentry\State\Hub $instance
                return $instance->getIntegration($className);
            }

            /**
             * Gets the ID of the last captured event.
             *
             * @static
             */
            public static function getLastEventId()
            {
                // @var \Sentry\State\Hub $instance
                return $instance->getLastEventId();
            }

            /**
             * Returns the span that is on the Hub.
             *
             * @static
             */
            public static function getSpan()
            {
                // @var \Sentry\State\Hub $instance
                return $instance->getSpan();
            }

            /**
             * Returns the transaction that is on the Hub.
             *
             * @psalm-suppress MoreSpecificReturnType
             * @psalm-suppress LessSpecificReturnStatement
             * @static
             */
            public static function getTransaction()
            {
                // @var \Sentry\State\Hub $instance
                return $instance->getTransaction();
            }

            /**
             * Removes a previously pushed scope from the stack. This restores the state
             * before the scope was pushed. All breadcrumbs and context information added
             * since the last call to {@see Hub::pushScope} are discarded.
             *
             * @static
             */
            public static function popScope()
            {
                // @var \Sentry\State\Hub $instance
                return $instance->popScope();
            }

            /**
             * Creates a new scope to store context information that will be layered on
             * top of the current one. It is isolated, i.e. all breadcrumbs and context
             * information added to this scope will be removed once the scope ends. Be
             * sure to always remove this scope with {@see Hub::popScope} when the
             * operation finishes or throws.
             *
             * @static
             */
            public static function pushScope()
            {
                // @var \Sentry\State\Hub $instance
                return $instance->pushScope();
            }

            /**
             * Sets the span on the Hub.
             *
             * @param \Sentry\State\Span|null $span The span
             * @static
             */
            public static function setSpan($span)
            {
                // @var \Sentry\State\Hub $instance
                return $instance->setSpan($span);
            }

            /**
             * Starts a new `Transaction` and returns it. This is the entry point to manual
             * tracing instrumentation.
             *
             * A tree structure can be built by adding child spans to the transaction, and
             * child spans to other spans. To start a new child span within the transaction
             * or any span, call the respective `startChild()` method.
             *
             * Every child span must be finished before the transaction is finished,
             * otherwise the unfinished spans are discarded.
             *
             * The transaction must be finished with a call to its `finish()` method, at
             * which point the transaction with all its finished child spans will be sent to
             * Sentry.
             *
             * @param \Sentry\State\array<string,  mixed> $customSamplingContext Additional context that will be passed to the {@see SamplingContext}
             * @param \Sentry\State\TransactionContext    $context               Properties of the new transaction
             * @param \Sentry\State\array<string,  mixed> $customSamplingContext Additional context that will be passed to the {@see SamplingContext}
             * @static
             */
            public static function startTransaction($context, $customSamplingContext = [])
            {
                // @var \Sentry\State\Hub $instance
                return $instance->startTransaction($context, $customSamplingContext);
            }

            /**
             * Creates a new scope with and executes the given operation within. The scope
             * is automatically removed once the operation finishes or throws.
             *
             * @param callable $callback The callback to be executed
             * @static
             */
            public static function withScope($callback)
            {
                // @var \Sentry\State\Hub $instance
                return $instance->withScope($callback);
            }
        }
}

namespace Yajra\DataTables\Facades {
        /**
         * @mixin \Yajra\DataTables\DataTables
         * @see \Yajra\DataTables\DataTables
         */
        class DataTables
        {
            /**
             * DataTables using Collection.
             *
             * @param  array|\Illuminate\Support\Collection                                      $collection
             * @return \Yajra\DataTables\CollectionDataTable|\Yajra\DataTables\DataTableAbstract
             * @static
             */
            public static function collection($collection)
            {
                // @var \Yajra\DataTables\DataTables $instance
                return $instance->collection($collection);
            }

            /**
             * DataTables using Eloquent Builder.
             *
             * @param  \Illuminate\Database\Eloquent\Builder|mixed                             $builder
             * @return \Yajra\DataTables\DataTableAbstract|\Yajra\DataTables\EloquentDataTable
             * @static
             */
            public static function eloquent($builder)
            {
                // @var \Yajra\DataTables\DataTables $instance
                return $instance->eloquent($builder);
            }

            /**
             * Get config instance.
             *
             * @return \Yajra\DataTables\Utilities\Config
             * @static
             */
            public static function getConfig()
            {
                // @var \Yajra\DataTables\DataTables $instance
                return $instance->getConfig();
            }

            /**
             * Get html builder instance.
             *
             * @throws \Exception
             * @return \Yajra\DataTables\Html\Builder
             * @static
             */
            public static function getHtmlBuilder()
            {
                // @var \Yajra\DataTables\DataTables $instance
                return $instance->getHtmlBuilder();
            }

            /**
             * Get request object.
             *
             * @return \Yajra\DataTables\Utilities\Request
             * @static
             */
            public static function getRequest()
            {
                // @var \Yajra\DataTables\DataTables $instance
                return $instance->getRequest();
            }

            /**
             * Checks if macro is registered.
             *
             * @param  string $name
             * @return bool
             * @static
             */
            public static function hasMacro($name)
            {
                return \Yajra\DataTables\DataTables::hasMacro($name);
            }

            /**
             * Register a custom macro.
             *
             * @param  string          $name
             * @param  callable|object $macro
             * @return void
             * @static
             */
            public static function macro($name, $macro)
            {
                \Yajra\DataTables\DataTables::macro($name, $macro);
            }

            /**
             * Make a DataTable instance from source.
             *
             * @param  mixed      $source
             * @throws \Exception
             * @return mixed
             * @static
             */
            public static function make($source)
            {
                return \Yajra\DataTables\DataTables::make($source);
            }

            /**
             * Mix another object into the class.
             *
             * @param  object               $mixin
             * @param  bool                 $replace
             * @throws \ReflectionException
             * @return void
             * @static
             */
            public static function mixin($mixin, $replace = true)
            {
                \Yajra\DataTables\DataTables::mixin($mixin, $replace);
            }

            /**
             * Make a DataTable instance from source.
             *
             * Alias of make for backward compatibility.
             *
             * @param  mixed      $source
             * @throws \Exception
             * @return mixed
             * @static
             */
            public static function of($source)
            {
                return \Yajra\DataTables\DataTables::of($source);
            }

            /**
             * DataTables using Query.
             *
             * @param  \Illuminate\Database\Query\Builder|mixed                             $builder
             * @return \Yajra\DataTables\DataTableAbstract|\Yajra\DataTables\QueryDataTable
             * @static
             */
            public static function query($builder)
            {
                // @var \Yajra\DataTables\DataTables $instance
                return $instance->query($builder);
            }

            /**
             * @deprecated please use query() instead, this method will be removed in a next version
             * @param $builder
             * @return \Yajra\DataTables\QueryDataTable
             * @static
             */
            public static function queryBuilder($builder)
            {
                // @var \Yajra\DataTables\DataTables $instance
                return $instance->queryBuilder($builder);
            }

            /**
             * DataTables using Collection.
             *
             * @param  array|\Illuminate\Http\Resources\Json\AnonymousResourceCollection          $collection
             * @param  mixed                                                                      $resource
             * @return \Yajra\DataTables\ApiResourceDataTable|\Yajra\DataTables\DataTableAbstract
             * @static
             */
            public static function resource($resource)
            {
                // @var \Yajra\DataTables\DataTables $instance
                return $instance->resource($resource);
            }
        }
}

namespace Illuminate\Http {
        class Request
        {
            /**
             * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
             * @static
             */
            public static function hasValidRelativeSignature()
            {
                return \Illuminate\Http\Request::hasValidRelativeSignature();
            }

            /**
             * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
             * @param mixed $absolute
             * @static
             */
            public static function hasValidSignature($absolute = true)
            {
                return \Illuminate\Http\Request::hasValidSignature($absolute);
            }

            /**
             * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
             * @param array $rules
             * @param mixed $params
             * @static
             */
            public static function validate($rules, ...$params)
            {
                return \Illuminate\Http\Request::validate($rules, ...$params);
            }

            /**
             * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
             * @param string $errorBag
             * @param array  $rules
             * @param mixed  $params
             * @static
             */
            public static function validateWithBag($errorBag, $rules, ...$params)
            {
                return \Illuminate\Http\Request::validateWithBag($errorBag, $rules, ...$params);
            }
        }
}

namespace Illuminate\Support {
        class Collection
        {
            /**
             * @see \Maatwebsite\Excel\Mixins\DownloadCollection::downloadExcel()
             * @param string      $fileName
             * @param string|null $writerType
             * @param mixed       $withHeadings
             * @static
             */
            public static function downloadExcel($fileName, $writerType = null, $withHeadings = false)
            {
                return \Illuminate\Support\Collection::downloadExcel($fileName, $writerType, $withHeadings);
            }

            /**
             * @see \Laravel\Nova\NovaServiceProvider::registerCollectionMacros()
             * @static
             */
            public static function isAssoc()
            {
                return \Illuminate\Support\Collection::isAssoc();
            }

            /**
             * @see \Maatwebsite\Excel\Mixins\StoreCollection::storeExcel()
             * @param string      $filePath
             * @param string|null $disk
             * @param string|null $writerType
             * @param mixed       $withHeadings
             * @static
             */
            public static function storeExcel($filePath, $disk = null, $writerType = null, $withHeadings = false)
            {
                return \Illuminate\Support\Collection::storeExcel($filePath, $disk, $writerType, $withHeadings);
            }
        }
}

namespace Illuminate\Routing {
        /**
         * @mixin \Illuminate\Routing\RouteRegistrar
         */
        class Router
        {
            /**
             * @see \Laravel\Ui\AuthRouteMethods::auth()
             * @param mixed $options
             * @static
             */
            public static function auth($options = [])
            {
                return \Illuminate\Routing\Router::auth($options);
            }

            /**
             * @see \Laravel\Ui\AuthRouteMethods::confirmPassword()
             * @static
             */
            public static function confirmPassword()
            {
                return \Illuminate\Routing\Router::confirmPassword();
            }

            /**
             * @see \Laravel\Ui\AuthRouteMethods::emailVerification()
             * @static
             */
            public static function emailVerification()
            {
                return \Illuminate\Routing\Router::emailVerification();
            }

            /**
             * @see \Lab404\Impersonate\ImpersonateServiceProvider::registerRoutesMacro()
             * @static
             */
            public static function impersonate()
            {
                return \Illuminate\Routing\Router::impersonate();
            }

            /**
             * @see \Laravel\Ui\AuthRouteMethods::resetPassword()
             * @static
             */
            public static function resetPassword()
            {
                return \Illuminate\Routing\Router::resetPassword();
            }
        }
}

namespace Illuminate\Validation {
        class Rule
        {
            /**
             * @see \Propaganistas\LaravelPhone\PhoneServiceProvider::boot()
             * @static
             */
            public static function phone()
            {
                return \Illuminate\Validation\Rule::phone();
            }
        }
}

namespace Illuminate\Console\Scheduling {
        class Event
        {
            /**
             * @see \Spatie\ScheduleMonitor\ScheduleMonitorServiceProvider::registerSchedulerEventMacros()
             * @static
             */
            public static function doNotMonitor()
            {
                return \Illuminate\Console\Scheduling\Event::doNotMonitor();
            }

            /**
             * @see \Spatie\ScheduleMonitor\ScheduleMonitorServiceProvider::registerSchedulerEventMacros()
             * @param int $graceTimeInMinutes
             * @static
             */
            public static function graceTimeInMinutes($graceTimeInMinutes)
            {
                return \Illuminate\Console\Scheduling\Event::graceTimeInMinutes($graceTimeInMinutes);
            }

            /**
             * @see \Spatie\ScheduleMonitor\ScheduleMonitorServiceProvider::registerSchedulerEventMacros()
             * @param string $monitorName
             * @static
             */
            public static function monitorName($monitorName)
            {
                return \Illuminate\Console\Scheduling\Event::monitorName($monitorName);
            }
        }
}

namespace  {
            class App extends \Illuminate\Support\Facades\App
            {
            }
            class Artisan extends \Illuminate\Support\Facades\Artisan
            {
            }
            class Auth extends \Illuminate\Support\Facades\Auth
            {
            }
            class Blade extends \Illuminate\Support\Facades\Blade
            {
            }
            class Broadcast extends \Illuminate\Support\Facades\Broadcast
            {
            }
            class Bus extends \Illuminate\Support\Facades\Bus
            {
            }
            class Cache extends \Illuminate\Support\Facades\Cache
            {
            }
            class Config extends \Illuminate\Support\Facades\Config
            {
            }
            class Cookie extends \Illuminate\Support\Facades\Cookie
            {
            }
            class Crypt extends \Illuminate\Support\Facades\Crypt
            {
            }
            class DB extends \Illuminate\Support\Facades\DB
            {
            }
            class Eloquent extends \Illuminate\Database\Eloquent\Model
            {
                /**
                 * Add a binding to the query.
                 *
                 * @param  mixed                              $value
                 * @param  string                             $type
                 * @throws \InvalidArgumentException
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function addBinding($value, $type = 'where')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->addBinding($value, $type);
                }

                /**
                 * Add another query builder as a nested where to the query builder.
                 *
                 * @param  \Illuminate\Database\Query\Builder $query
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function addNestedWhereQuery($query, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->addNestedWhereQuery($query, $boolean);
                }

                /**
                 * Add a new select column to the query.
                 *
                 * @param  array|mixed                        $column
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function addSelect($column)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->addSelect($column);
                }

                /**
                 * Add an exists clause to the query.
                 *
                 * @param  \Illuminate\Database\Query\Builder $query
                 * @param  string                             $boolean
                 * @param  bool                               $not
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function addWhereExistsQuery($query, $boolean = 'and', $not = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->addWhereExistsQuery($query, $boolean, $not);
                }

                /**
                 * Execute an aggregate function on the database.
                 *
                 * @param  string $function
                 * @param  array  $columns
                 * @return mixed
                 * @static
                 */
                public static function aggregate($function, $columns = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->aggregate($function, $columns);
                }

                /**
                 * Apply the scopes to the Eloquent builder instance and return it.
                 *
                 * @return static
                 * @static
                 */
                public static function applyScopes()
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->applyScopes();
                }

                /**
                 * Alias for the "avg" method.
                 *
                 * @param  string $column
                 * @return mixed
                 * @static
                 */
                public static function average($column)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->average($column);
                }

                /**
                 * Retrieve the average of the values of a given column.
                 *
                 * @param  string $column
                 * @return mixed
                 * @static
                 */
                public static function avg($column)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->avg($column);
                }

                /**
                 * Execute the query and get the first result if it's the sole matching record.
                 *
                 * @param  array|string                                           $columns
                 * @throws \Illuminate\Database\RecordsNotFoundException
                 * @throws \Illuminate\Database\MultipleRecordsFoundException
                 * @return \Illuminate\Database\Eloquent\Model|object|static|null
                 * @static
                 */
                public static function baseSole($columns = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->baseSole($columns);
                }

                /**
                 * Chunk the results of the query.
                 *
                 * @param  int      $count
                 * @param  callable $callback
                 * @return bool
                 * @static
                 */
                public static function chunk($count, $callback)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->chunk($count, $callback);
                }

                /**
                 * Chunk the results of a query by comparing IDs.
                 *
                 * @param  int         $count
                 * @param  callable    $callback
                 * @param  string|null $column
                 * @param  string|null $alias
                 * @return bool
                 * @static
                 */
                public static function chunkById($count, $callback, $column = null, $alias = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->chunkById($count, $callback, $column, $alias);
                }

                /**
                 * Run a map over each item while chunking.
                 *
                 * @param  callable                       $callback
                 * @param  int                            $count
                 * @return \Illuminate\Support\Collection
                 * @static
                 */
                public static function chunkMap($callback, $count = 1000)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->chunkMap($callback, $count);
                }

                /**
                 * Remove all of the expressions from a list of bindings.
                 *
                 * @param  array $bindings
                 * @return array
                 * @static
                 */
                public static function cleanBindings($bindings)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->cleanBindings($bindings);
                }

                /**
                 * Clone the query.
                 *
                 * @return static
                 * @static
                 */
                public static function clone()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->clone();
                }

                /**
                 * Clone the query without the given properties.
                 *
                 * @param  array  $properties
                 * @return static
                 * @static
                 */
                public static function cloneWithout($properties)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->cloneWithout($properties);
                }

                /**
                 * Clone the query without the given bindings.
                 *
                 * @param  array  $except
                 * @return static
                 * @static
                 */
                public static function cloneWithoutBindings($except)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->cloneWithoutBindings($except);
                }

                /**
                 * Retrieve the "count" result of the query.
                 *
                 * @param  string $columns
                 * @return int
                 * @static
                 */
                public static function count($columns = '*')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->count($columns);
                }

                /**
                 * Save a new model and return the instance.
                 *
                 * @param  array                                     $attributes
                 * @return $this|\Illuminate\Database\Eloquent\Model
                 * @static
                 */
                public static function create($attributes = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->create($attributes);
                }

                /**
                 * Add a "cross join" clause to the query.
                 *
                 * @param  string                             $table
                 * @param  \Closure|string|null               $first
                 * @param  string|null                        $operator
                 * @param  string|null                        $second
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function crossJoin($table, $first = null, $operator = null, $second = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->crossJoin($table, $first, $operator, $second);
                }

                /**
                 * Add a subquery cross join to the query.
                 *
                 * @param  \Closure|\Illuminate\Database\Query\Builder|string $query
                 * @param  string                                             $as
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function crossJoinSub($query, $as)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->crossJoinSub($query, $as);
                }

                /**
                 * Get a lazy collection for the given query.
                 *
                 * @return \Illuminate\Support\LazyCollection
                 * @static
                 */
                public static function cursor()
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->cursor();
                }

                /**
                 * Die and dump the current SQL and bindings.
                 *
                 * @return void
                 * @static
                 */
                public static function dd()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    $instance->dd();
                }

                /**
                 * Force the query to only return distinct results.
                 *
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function distinct()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->distinct();
                }

                /**
                 * Determine if no rows exist for the current query.
                 *
                 * @return bool
                 * @static
                 */
                public static function doesntExist()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->doesntExist();
                }

                /**
                 * Execute the given callback if rows exist for the current query.
                 *
                 * @param  \Closure $callback
                 * @return mixed
                 * @static
                 */
                public static function doesntExistOr($callback)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->doesntExistOr($callback);
                }

                /**
                 * Add a relationship count / exists condition to the query.
                 *
                 * @param  string                                       $relation
                 * @param  string                                       $boolean
                 * @param  \Closure|null                                $callback
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function doesntHave($relation, $boolean = 'and', $callback = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->doesntHave($relation, $boolean, $callback);
                }

                /**
                 * Add a polymorphic relationship count / exists condition to the query.
                 *
                 * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string $relation
                 * @param  array|string                                           $types
                 * @param  string                                                 $boolean
                 * @param  \Closure|null                                          $callback
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function doesntHaveMorph($relation, $types, $boolean = 'and', $callback = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->doesntHaveMorph($relation, $types, $boolean, $callback);
                }

                /**
                 * Dump the current SQL and bindings.
                 *
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function dump()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->dump();
                }

                /**
                 * Handles dynamic "where" clauses to the query.
                 *
                 * @param  string                             $method
                 * @param  array                              $parameters
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function dynamicWhere($method, $parameters)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->dynamicWhere($method, $parameters);
                }

                /**
                 * Execute a callback over each item while chunking.
                 *
                 * @param  callable $callback
                 * @param  int      $count
                 * @return bool
                 * @static
                 */
                public static function each($callback, $count = 1000)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->each($callback, $count);
                }

                /**
                 * Execute a callback over each item while chunking by ID.
                 *
                 * @param  callable    $callback
                 * @param  int         $count
                 * @param  string|null $column
                 * @param  string|null $alias
                 * @return bool
                 * @static
                 */
                public static function eachById($callback, $count = 1000, $column = null, $alias = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->eachById($callback, $count, $column, $alias);
                }

                /**
                 * Eager load the relationships for the models.
                 *
                 * @param  array $models
                 * @return array
                 * @static
                 */
                public static function eagerLoadRelations($models)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->eagerLoadRelations($models);
                }

                /**
                 * Determine if any rows exist for the current query.
                 *
                 * @return bool
                 * @static
                 */
                public static function exists()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->exists();
                }

                /**
                 * Execute the given callback if no rows exist for the current query.
                 *
                 * @param  \Closure $callback
                 * @return mixed
                 * @static
                 */
                public static function existsOr($callback)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->existsOr($callback);
                }

                /**
                 * Explains the query.
                 *
                 * @return \Illuminate\Support\Collection
                 * @static
                 */
                public static function explain()
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->explain();
                }

                /**
                 * Find a model by its primary key.
                 *
                 * @param  mixed                                                                                             $id
                 * @param  array                                                                                             $columns
                 * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|static|static[]|null
                 * @static
                 */
                public static function find($id, $columns = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->find($id, $columns);
                }

                /**
                 * Find multiple models by their primary keys.
                 *
                 * @param  array|\Illuminate\Contracts\Support\Arrayable $ids
                 * @param  array                                         $columns
                 * @return \Illuminate\Database\Eloquent\Collection
                 * @static
                 */
                public static function findMany($ids, $columns = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->findMany($ids, $columns);
                }

                /**
                 * Find a model by its primary key or throw an exception.
                 *
                 * @param  mixed                                                                                        $id
                 * @param  array                                                                                        $columns
                 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
                 * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|static|static[]
                 * @static
                 */
                public static function findOrFail($id, $columns = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->findOrFail($id, $columns);
                }

                /**
                 * Find a model by its primary key or return fresh model instance.
                 *
                 * @param  mixed                                      $id
                 * @param  array                                      $columns
                 * @return \Illuminate\Database\Eloquent\Model|static
                 * @static
                 */
                public static function findOrNew($id, $columns = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->findOrNew($id, $columns);
                }

                /**
                 * Execute the query and get the first result.
                 *
                 * @param  array|string                                           $columns
                 * @return \Illuminate\Database\Eloquent\Model|object|static|null
                 * @static
                 */
                public static function first($columns = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->first($columns);
                }

                /**
                 * Execute the query and get the first result or call a callback.
                 *
                 * @param  array|\Closure                                   $columns
                 * @param  \Closure|null                                    $callback
                 * @return \Illuminate\Database\Eloquent\Model|mixed|static
                 * @static
                 */
                public static function firstOr($columns = [], $callback = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->firstOr($columns, $callback);
                }

                /**
                 * Get the first record matching the attributes or create it.
                 *
                 * @param  array                                      $attributes
                 * @param  array                                      $values
                 * @return \Illuminate\Database\Eloquent\Model|static
                 * @static
                 */
                public static function firstOrCreate($attributes = [], $values = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->firstOrCreate($attributes, $values);
                }

                /**
                 * Execute the query and get the first result or throw an exception.
                 *
                 * @param  array                                                $columns
                 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
                 * @return \Illuminate\Database\Eloquent\Model|static
                 * @static
                 */
                public static function firstOrFail($columns = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->firstOrFail($columns);
                }

                /**
                 * Get the first record matching the attributes or instantiate it.
                 *
                 * @param  array                                      $attributes
                 * @param  array                                      $values
                 * @return \Illuminate\Database\Eloquent\Model|static
                 * @static
                 */
                public static function firstOrNew($attributes = [], $values = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->firstOrNew($attributes, $values);
                }

                /**
                 * Add a basic where clause to the query, and return the first result.
                 *
                 * @param  array|\Closure|\Illuminate\Database\Query\Expression|string $column
                 * @param  mixed                                                       $operator
                 * @param  mixed                                                       $value
                 * @param  string                                                      $boolean
                 * @return \Illuminate\Database\Eloquent\Model|static
                 * @static
                 */
                public static function firstWhere($column, $operator = null, $value = null, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->firstWhere($column, $operator, $value, $boolean);
                }

                /**
                 * Save a new model and return the instance. Allow mass-assignment.
                 *
                 * @param  array                                     $attributes
                 * @return $this|\Illuminate\Database\Eloquent\Model
                 * @static
                 */
                public static function forceCreate($attributes)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->forceCreate($attributes);
                }

                /**
                 * Create a new query instance for nested where condition.
                 *
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function forNestedWhere()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->forNestedWhere();
                }

                /**
                 * Set the limit and offset for a given page.
                 *
                 * @param  int                                $page
                 * @param  int                                $perPage
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function forPage($page, $perPage = 15)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->forPage($page, $perPage);
                }

                /**
                 * Constrain the query to the next "page" of results after a given ID.
                 *
                 * @param  int                                $perPage
                 * @param  int|null                           $lastId
                 * @param  string                             $column
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->forPageAfterId($perPage, $lastId, $column);
                }

                /**
                 * Constrain the query to the previous "page" of results before a given ID.
                 *
                 * @param  int                                $perPage
                 * @param  int|null                           $lastId
                 * @param  string                             $column
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function forPageBeforeId($perPage = 15, $lastId = 0, $column = 'id')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->forPageBeforeId($perPage, $lastId, $column);
                }

                /**
                 * Set the table which the query is targeting.
                 *
                 * @param  \Closure|\Illuminate\Database\Query\Builder|string $table
                 * @param  string|null                                        $as
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function from($table, $as = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->from($table, $as);
                }

                /**
                 * Create a collection of models from a raw query.
                 *
                 * @param  string                                   $query
                 * @param  array                                    $bindings
                 * @return \Illuminate\Database\Eloquent\Collection
                 * @static
                 */
                public static function fromQuery($query, $bindings = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->fromQuery($query, $bindings);
                }

                /**
                 * Add a raw from clause to the query.
                 *
                 * @param  string                             $expression
                 * @param  mixed                              $bindings
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function fromRaw($expression, $bindings = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->fromRaw($expression, $bindings);
                }

                /**
                 * Makes "from" fetch from a subquery.
                 *
                 * @param  \Closure|\Illuminate\Database\Query\Builder|string $query
                 * @param  string                                             $as
                 * @throws \InvalidArgumentException
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function fromSub($query, $as)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->fromSub($query, $as);
                }

                /**
                 * Execute the query as a "select" statement.
                 *
                 * @param  array|string                                      $columns
                 * @return \Illuminate\Database\Eloquent\Collection|static[]
                 * @static
                 */
                public static function get($columns = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->get($columns);
                }

                /**
                 * Get the current query value bindings in a flattened array.
                 *
                 * @return array
                 * @static
                 */
                public static function getBindings()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->getBindings();
                }

                /**
                 * Get the count of the total records for the paginator.
                 *
                 * @param  array $columns
                 * @return int
                 * @static
                 */
                public static function getCountForPagination($columns = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->getCountForPagination($columns);
                }

                /**
                 * Get the relationships being eagerly loaded.
                 *
                 * @return array
                 * @static
                 */
                public static function getEagerLoads()
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->getEagerLoads();
                }

                /**
                 * Get the given global macro by name.
                 *
                 * @param  string   $name
                 * @return \Closure
                 * @static
                 */
                public static function getGlobalMacro($name)
                {
                    return \Illuminate\Database\Eloquent\Builder::getGlobalMacro($name);
                }

                /**
                 * Get the query grammar instance.
                 *
                 * @return \Illuminate\Database\Query\Grammars\Grammar
                 * @static
                 */
                public static function getGrammar()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->getGrammar();
                }

                /**
                 * Get the given macro by name.
                 *
                 * @param  string   $name
                 * @return \Closure
                 * @static
                 */
                public static function getMacro($name)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->getMacro($name);
                }

                /**
                 * Get the model instance being queried.
                 *
                 * @return \Illuminate\Database\Eloquent\Model|static
                 * @static
                 */
                public static function getModel()
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->getModel();
                }

                /**
                 * Get the hydrated models without eager loading.
                 *
                 * @param  array|string                                   $columns
                 * @return \Illuminate\Database\Eloquent\Model[]|static[]
                 * @static
                 */
                public static function getModels($columns = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->getModels($columns);
                }

                /**
                 * Get the database query processor instance.
                 *
                 * @return \Illuminate\Database\Query\Processors\Processor
                 * @static
                 */
                public static function getProcessor()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->getProcessor();
                }

                /**
                 * Get the underlying query builder instance.
                 *
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function getQuery()
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->getQuery();
                }

                /**
                 * Get the raw array of bindings.
                 *
                 * @return array
                 * @static
                 */
                public static function getRawBindings()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->getRawBindings();
                }

                /**
                 * Add a "group by" clause to the query.
                 *
                 * @param  array|string                       $groups
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function groupBy(...$groups)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->groupBy(...$groups);
                }

                /**
                 * Add a raw groupBy clause to the query.
                 *
                 * @param  string                             $sql
                 * @param  array                              $bindings
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function groupByRaw($sql, $bindings = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->groupByRaw($sql, $bindings);
                }

                /**
                 * Add a relationship count / exists condition to the query.
                 *
                 * @param  \Illuminate\Database\Eloquent\Relations\Relation|string $relation
                 * @param  string                                                  $operator
                 * @param  int                                                     $count
                 * @param  string                                                  $boolean
                 * @param  \Closure|null                                           $callback
                 * @throws \RuntimeException
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function has($relation, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->has($relation, $operator, $count, $boolean, $callback);
                }

                /**
                 * Checks if a global macro is registered.
                 *
                 * @param  string $name
                 * @return bool
                 * @static
                 */
                public static function hasGlobalMacro($name)
                {
                    return \Illuminate\Database\Eloquent\Builder::hasGlobalMacro($name);
                }

                /**
                 * Checks if a macro is registered.
                 *
                 * @param  string $name
                 * @return bool
                 * @static
                 */
                public static function hasMacro($name)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->hasMacro($name);
                }

                /**
                 * Add a polymorphic relationship count / exists condition to the query.
                 *
                 * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string $relation
                 * @param  array|string                                           $types
                 * @param  string                                                 $operator
                 * @param  int                                                    $count
                 * @param  string                                                 $boolean
                 * @param  \Closure|null                                          $callback
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->hasMorph($relation, $types, $operator, $count, $boolean, $callback);
                }

                /**
                 * Add a "having" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  string|null                        $operator
                 * @param  string|null                        $value
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function having($column, $operator = null, $value = null, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->having($column, $operator, $value, $boolean);
                }

                /**
                 * Add a "having between " clause to the query.
                 *
                 * @param  string                             $column
                 * @param  array                              $values
                 * @param  string                             $boolean
                 * @param  bool                               $not
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function havingBetween($column, $values, $boolean = 'and', $not = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->havingBetween($column, $values, $boolean, $not);
                }

                /**
                 * Add a raw having clause to the query.
                 *
                 * @param  string                             $sql
                 * @param  array                              $bindings
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function havingRaw($sql, $bindings = [], $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->havingRaw($sql, $bindings, $boolean);
                }

                /**
                 * Create a collection of models from plain arrays.
                 *
                 * @param  array                                    $items
                 * @return \Illuminate\Database\Eloquent\Collection
                 * @static
                 */
                public static function hydrate($items)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->hydrate($items);
                }

                /**
                 * Concatenate values of a given column as a string.
                 *
                 * @param  string $column
                 * @param  string $glue
                 * @return string
                 * @static
                 */
                public static function implode($column, $glue = '')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->implode($column, $glue);
                }

                /**
                 * Put the query's results in random order.
                 *
                 * @param  string                             $seed
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function inRandomOrder($seed = '')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->inRandomOrder($seed);
                }

                /**
                 * Insert new records into the database.
                 *
                 * @param  array $values
                 * @return bool
                 * @static
                 */
                public static function insert($values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->insert($values);
                }

                /**
                 * Insert a new record and get the value of the primary key.
                 *
                 * @param  array       $values
                 * @param  string|null $sequence
                 * @return int
                 * @static
                 */
                public static function insertGetId($values, $sequence = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->insertGetId($values, $sequence);
                }

                /**
                 * Insert new records into the database while ignoring errors.
                 *
                 * @param  array $values
                 * @return int
                 * @static
                 */
                public static function insertOrIgnore($values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->insertOrIgnore($values);
                }

                /**
                 * Insert new records into the table using a subquery.
                 *
                 * @param  array                                              $columns
                 * @param  \Closure|\Illuminate\Database\Query\Builder|string $query
                 * @return int
                 * @static
                 */
                public static function insertUsing($columns, $query)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->insertUsing($columns, $query);
                }

                /**
                 * Add a join clause to the query.
                 *
                 * @param  string                             $table
                 * @param  \Closure|string                    $first
                 * @param  string|null                        $operator
                 * @param  string|null                        $second
                 * @param  string                             $type
                 * @param  bool                               $where
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->join($table, $first, $operator, $second, $type, $where);
                }

                /**
                 * Add a subquery join clause to the query.
                 *
                 * @param  \Closure|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|string $query
                 * @param  string                                                                                   $as
                 * @param  \Closure|string                                                                          $first
                 * @param  string|null                                                                              $operator
                 * @param  string|null                                                                              $second
                 * @param  string                                                                                   $type
                 * @param  bool                                                                                     $where
                 * @throws \InvalidArgumentException
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->joinSub($query, $as, $first, $operator, $second, $type, $where);
                }

                /**
                 * Add a "join where" clause to the query.
                 *
                 * @param  string                             $table
                 * @param  \Closure|string                    $first
                 * @param  string                             $operator
                 * @param  string                             $second
                 * @param  string                             $type
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function joinWhere($table, $first, $operator, $second, $type = 'inner')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->joinWhere($table, $first, $operator, $second, $type);
                }

                /**
                 * Add an "order by" clause for a timestamp to the query.
                 *
                 * @param  \Illuminate\Database\Query\Expression|string $column
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function latest($column = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->latest($column);
                }

                /**
                 * Add a left join to the query.
                 *
                 * @param  string                             $table
                 * @param  \Closure|string                    $first
                 * @param  string|null                        $operator
                 * @param  string|null                        $second
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function leftJoin($table, $first, $operator = null, $second = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->leftJoin($table, $first, $operator, $second);
                }

                /**
                 * Add a subquery left join to the query.
                 *
                 * @param  \Closure|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|string $query
                 * @param  string                                                                                   $as
                 * @param  \Closure|string                                                                          $first
                 * @param  string|null                                                                              $operator
                 * @param  string|null                                                                              $second
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function leftJoinSub($query, $as, $first, $operator = null, $second = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->leftJoinSub($query, $as, $first, $operator, $second);
                }

                /**
                 * Add a "join where" clause to the query.
                 *
                 * @param  string                             $table
                 * @param  \Closure|string                    $first
                 * @param  string                             $operator
                 * @param  string                             $second
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function leftJoinWhere($table, $first, $operator, $second)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->leftJoinWhere($table, $first, $operator, $second);
                }

                /**
                 * Set the "limit" value of the query.
                 *
                 * @param  int                                $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function limit($value)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->limit($value);
                }

                /**
                 * Lock the selected rows in the table.
                 *
                 * @param  bool|string                        $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function lock($value = true)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->lock($value);
                }

                /**
                 * Lock the selected rows in the table for updating.
                 *
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function lockForUpdate()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->lockForUpdate();
                }

                /**
                 * Register a custom macro.
                 *
                 * @param  string          $name
                 * @param  callable|object $macro
                 * @return void
                 * @static
                 */
                public static function macro($name, $macro)
                {
                    \Illuminate\Database\Query\Builder::macro($name, $macro);
                }

                /**
                 * Dynamically handle calls to the class.
                 *
                 * @param  string                  $method
                 * @param  array                   $parameters
                 * @throws \BadMethodCallException
                 * @return mixed
                 * @static
                 */
                public static function macroCall($method, $parameters)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->macroCall($method, $parameters);
                }

                /**
                 * Create and return an un-saved model instance.
                 *
                 * @param  array                                      $attributes
                 * @return \Illuminate\Database\Eloquent\Model|static
                 * @static
                 */
                public static function make($attributes = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->make($attributes);
                }

                /**
                 * Retrieve the maximum value of a given column.
                 *
                 * @param  string $column
                 * @return mixed
                 * @static
                 */
                public static function max($column)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->max($column);
                }

                /**
                 * Merge an array of bindings into our bindings.
                 *
                 * @param  \Illuminate\Database\Query\Builder $query
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function mergeBindings($query)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->mergeBindings($query);
                }

                /**
                 * Merge the where constraints from another query to the current query.
                 *
                 * @param  \Illuminate\Database\Eloquent\Builder        $from
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function mergeConstraintsFrom($from)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->mergeConstraintsFrom($from);
                }

                /**
                 * Merge an array of where clauses and bindings.
                 *
                 * @param  array $wheres
                 * @param  array $bindings
                 * @return void
                 * @static
                 */
                public static function mergeWheres($wheres, $bindings)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    $instance->mergeWheres($wheres, $bindings);
                }

                /**
                 * Retrieve the minimum value of a given column.
                 *
                 * @param  string $column
                 * @return mixed
                 * @static
                 */
                public static function min($column)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->min($column);
                }

                /**
                 * Mix another object into the class.
                 *
                 * @param  object               $mixin
                 * @param  bool                 $replace
                 * @throws \ReflectionException
                 * @return void
                 * @static
                 */
                public static function mixin($mixin, $replace = true)
                {
                    \Illuminate\Database\Query\Builder::mixin($mixin, $replace);
                }

                /**
                 * Create a new instance of the model being queried.
                 *
                 * @param  array                                      $attributes
                 * @return \Illuminate\Database\Eloquent\Model|static
                 * @static
                 */
                public static function newModelInstance($attributes = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->newModelInstance($attributes);
                }

                /**
                 * Execute a numeric aggregate function on the database.
                 *
                 * @param  string    $function
                 * @param  array     $columns
                 * @return float|int
                 * @static
                 */
                public static function numericAggregate($function, $columns = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->numericAggregate($function, $columns);
                }

                /**
                 * Set the "offset" value of the query.
                 *
                 * @param  int                                $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function offset($value)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->offset($value);
                }

                /**
                 * Add an "order by" clause for a timestamp to the query.
                 *
                 * @param  \Illuminate\Database\Query\Expression|string $column
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function oldest($column = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->oldest($column);
                }

                /**
                 * Register a replacement for the default delete function.
                 *
                 * @param  \Closure $callback
                 * @return void
                 * @static
                 */
                public static function onDelete($callback)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    $instance->onDelete($callback);
                }

                /**
                 * Add an "order by" clause to the query.
                 *
                 * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Query\Expression|string $column
                 * @param  string                                                                                   $direction
                 * @throws \InvalidArgumentException
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orderBy($column, $direction = 'asc')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orderBy($column, $direction);
                }

                /**
                 * Add a descending "order by" clause to the query.
                 *
                 * @param  string                             $column
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orderByDesc($column)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orderByDesc($column);
                }

                /**
                 * Add a raw "order by" clause to the query.
                 *
                 * @param  string                             $sql
                 * @param  array                              $bindings
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orderByRaw($sql, $bindings = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orderByRaw($sql, $bindings);
                }

                /**
                 * Add a relationship count / exists condition to the query with an "or".
                 *
                 * @param  string                                       $relation
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function orDoesntHave($relation)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->orDoesntHave($relation);
                }

                /**
                 * Add a polymorphic relationship count / exists condition to the query with an "or".
                 *
                 * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string $relation
                 * @param  array|string                                           $types
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function orDoesntHaveMorph($relation, $types)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->orDoesntHaveMorph($relation, $types);
                }

                /**
                 * Add a relationship count / exists condition to the query with an "or".
                 *
                 * @param  string                                       $relation
                 * @param  string                                       $operator
                 * @param  int                                          $count
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function orHas($relation, $operator = '>=', $count = 1)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->orHas($relation, $operator, $count);
                }

                /**
                 * Add a polymorphic relationship count / exists condition to the query with an "or".
                 *
                 * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string $relation
                 * @param  array|string                                           $types
                 * @param  string                                                 $operator
                 * @param  int                                                    $count
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function orHasMorph($relation, $types, $operator = '>=', $count = 1)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->orHasMorph($relation, $types, $operator, $count);
                }

                /**
                 * Add an "or having" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  string|null                        $operator
                 * @param  string|null                        $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orHaving($column, $operator = null, $value = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orHaving($column, $operator, $value);
                }

                /**
                 * Add a raw or having clause to the query.
                 *
                 * @param  string                             $sql
                 * @param  array                              $bindings
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orHavingRaw($sql, $bindings = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orHavingRaw($sql, $bindings);
                }

                /**
                 * Add an "or where" clause to the query.
                 *
                 * @param  array|\Closure|\Illuminate\Database\Query\Expression|string $column
                 * @param  mixed                                                       $operator
                 * @param  mixed                                                       $value
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function orWhere($column, $operator = null, $value = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->orWhere($column, $operator, $value);
                }

                /**
                 * Add an or where between statement to the query.
                 *
                 * @param  string                             $column
                 * @param  array                              $values
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereBetween($column, $values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereBetween($column, $values);
                }

                /**
                 * Add an or where between statement using columns to the query.
                 *
                 * @param  string                             $column
                 * @param  array                              $values
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereBetweenColumns($column, $values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereBetweenColumns($column, $values);
                }

                /**
                 * Add an "or where" clause comparing two columns to the query.
                 *
                 * @param  array|string                       $first
                 * @param  string|null                        $operator
                 * @param  string|null                        $second
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereColumn($first, $operator = null, $second = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereColumn($first, $operator, $second);
                }

                /**
                 * Add an "or where date" statement to the query.
                 *
                 * @param  string                             $column
                 * @param  string                             $operator
                 * @param  \DateTimeInterface|string|null     $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereDate($column, $operator, $value = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereDate($column, $operator, $value);
                }

                /**
                 * Add an "or where day" statement to the query.
                 *
                 * @param  string                             $column
                 * @param  string                             $operator
                 * @param  \DateTimeInterface|string|null     $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereDay($column, $operator, $value = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereDay($column, $operator, $value);
                }

                /**
                 * Add a relationship count / exists condition to the query with where clauses and an "or".
                 *
                 * @param  string                                       $relation
                 * @param  \Closure|null                                $callback
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function orWhereDoesntHave($relation, $callback = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->orWhereDoesntHave($relation, $callback);
                }

                /**
                 * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
                 *
                 * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string $relation
                 * @param  array|string                                           $types
                 * @param  \Closure|null                                          $callback
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function orWhereDoesntHaveMorph($relation, $types, $callback = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->orWhereDoesntHaveMorph($relation, $types, $callback);
                }

                /**
                 * Add an or exists clause to the query.
                 *
                 * @param  \Closure                           $callback
                 * @param  bool                               $not
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereExists($callback, $not = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereExists($callback, $not);
                }

                /**
                 * Add a relationship count / exists condition to the query with where clauses and an "or".
                 *
                 * @param  string                                       $relation
                 * @param  \Closure|null                                $callback
                 * @param  string                                       $operator
                 * @param  int                                          $count
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function orWhereHas($relation, $callback = null, $operator = '>=', $count = 1)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->orWhereHas($relation, $callback, $operator, $count);
                }

                /**
                 * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
                 *
                 * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string $relation
                 * @param  array|string                                           $types
                 * @param  \Closure|null                                          $callback
                 * @param  string                                                 $operator
                 * @param  int                                                    $count
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function orWhereHasMorph($relation, $types, $callback = null, $operator = '>=', $count = 1)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->orWhereHasMorph($relation, $types, $callback, $operator, $count);
                }

                /**
                 * Add an "or where in" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  mixed                              $values
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereIn($column, $values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereIn($column, $values);
                }

                /**
                 * Add an "or where in raw" clause for integer values to the query.
                 *
                 * @param  string                                        $column
                 * @param  array|\Illuminate\Contracts\Support\Arrayable $values
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereIntegerInRaw($column, $values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereIntegerInRaw($column, $values);
                }

                /**
                 * Add an "or where not in raw" clause for integer values to the query.
                 *
                 * @param  string                                        $column
                 * @param  array|\Illuminate\Contracts\Support\Arrayable $values
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereIntegerNotInRaw($column, $values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereIntegerNotInRaw($column, $values);
                }

                /**
                 * Add an "or where JSON contains" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  mixed                              $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereJsonContains($column, $value)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereJsonContains($column, $value);
                }

                /**
                 * Add an "or where JSON not contains" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  mixed                              $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereJsonDoesntContain($column, $value)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereJsonDoesntContain($column, $value);
                }

                /**
                 * Add an "or where JSON length" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  mixed                              $operator
                 * @param  mixed                              $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereJsonLength($column, $operator, $value = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereJsonLength($column, $operator, $value);
                }

                /**
                 * Add an "or where month" statement to the query.
                 *
                 * @param  string                             $column
                 * @param  string                             $operator
                 * @param  \DateTimeInterface|string|null     $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereMonth($column, $operator, $value = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereMonth($column, $operator, $value);
                }

                /**
                 * Add an or where not between statement to the query.
                 *
                 * @param  string                             $column
                 * @param  array                              $values
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereNotBetween($column, $values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereNotBetween($column, $values);
                }

                /**
                 * Add an or where not between statement using columns to the query.
                 *
                 * @param  string                             $column
                 * @param  array                              $values
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereNotBetweenColumns($column, $values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereNotBetweenColumns($column, $values);
                }

                /**
                 * Add a where not exists clause to the query.
                 *
                 * @param  \Closure                           $callback
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereNotExists($callback)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereNotExists($callback);
                }

                /**
                 * Add an "or where not in" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  mixed                              $values
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereNotIn($column, $values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereNotIn($column, $values);
                }

                /**
                 * Add an "or where not null" clause to the query.
                 *
                 * @param  string                             $column
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereNotNull($column)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereNotNull($column);
                }

                /**
                 * Add an "or where null" clause to the query.
                 *
                 * @param  string                             $column
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereNull($column)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereNull($column);
                }

                /**
                 * Add a raw or where clause to the query.
                 *
                 * @param  string                             $sql
                 * @param  mixed                              $bindings
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereRaw($sql, $bindings = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereRaw($sql, $bindings);
                }

                /**
                 * Adds an or where condition using row values.
                 *
                 * @param  array                              $columns
                 * @param  string                             $operator
                 * @param  array                              $values
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereRowValues($columns, $operator, $values)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereRowValues($columns, $operator, $values);
                }

                /**
                 * Add an "or where time" statement to the query.
                 *
                 * @param  string                             $column
                 * @param  string                             $operator
                 * @param  \DateTimeInterface|string|null     $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereTime($column, $operator, $value = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereTime($column, $operator, $value);
                }

                /**
                 * Add an "or where year" statement to the query.
                 *
                 * @param  string                             $column
                 * @param  string                             $operator
                 * @param  \DateTimeInterface|int|string|null $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function orWhereYear($column, $operator, $value = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->orWhereYear($column, $operator, $value);
                }

                /**
                 * Paginate the given query.
                 *
                 * @param  int|null                                              $perPage
                 * @param  array                                                 $columns
                 * @param  string                                                $pageName
                 * @param  int|null                                              $page
                 * @throws \InvalidArgumentException
                 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
                 * @static
                 */
                public static function paginate($perPage = null, $columns = [], $pageName = 'page', $page = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->paginate($perPage, $columns, $pageName, $page);
                }

                /**
                 * Get an array with the values of a given column.
                 *
                 * @param  \Illuminate\Database\Query\Expression|string $column
                 * @param  string|null                                  $key
                 * @return \Illuminate\Support\Collection
                 * @static
                 */
                public static function pluck($column, $key = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->pluck($column, $key);
                }

                /**
                 * Prepare the value and operator for a where clause.
                 *
                 * @param  string                    $value
                 * @param  string                    $operator
                 * @param  bool                      $useDefault
                 * @throws \InvalidArgumentException
                 * @return array
                 * @static
                 */
                public static function prepareValueAndOperator($value, $operator, $useDefault = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->prepareValueAndOperator($value, $operator, $useDefault);
                }

                /**
                 * Create a raw database expression.
                 *
                 * @param  mixed                                 $value
                 * @return \Illuminate\Database\Query\Expression
                 * @static
                 */
                public static function raw($value)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->raw($value);
                }

                /**
                 * Get an array of global scopes that were removed from the query.
                 *
                 * @return array
                 * @static
                 */
                public static function removedScopes()
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->removedScopes();
                }

                /**
                 * Remove all existing orders and optionally add a new order.
                 *
                 * @param  string|null                        $column
                 * @param  string                             $direction
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function reorder($column = null, $direction = 'asc')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->reorder($column, $direction);
                }

                /**
                 * Add a right join to the query.
                 *
                 * @param  string                             $table
                 * @param  \Closure|string                    $first
                 * @param  string|null                        $operator
                 * @param  string|null                        $second
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function rightJoin($table, $first, $operator = null, $second = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->rightJoin($table, $first, $operator, $second);
                }

                /**
                 * Add a subquery right join to the query.
                 *
                 * @param  \Closure|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|string $query
                 * @param  string                                                                                   $as
                 * @param  \Closure|string                                                                          $first
                 * @param  string|null                                                                              $operator
                 * @param  string|null                                                                              $second
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function rightJoinSub($query, $as, $first, $operator = null, $second = null)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->rightJoinSub($query, $as, $first, $operator, $second);
                }

                /**
                 * Add a "right join where" clause to the query.
                 *
                 * @param  string                             $table
                 * @param  \Closure|string                    $first
                 * @param  string                             $operator
                 * @param  string                             $second
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function rightJoinWhere($table, $first, $operator, $second)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->rightJoinWhere($table, $first, $operator, $second);
                }

                /**
                 * Call the given local model scopes.
                 *
                 * @param  array|string $scopes
                 * @return mixed|static
                 * @static
                 */
                public static function scopes($scopes)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->scopes($scopes);
                }

                /**
                 * Set the columns to be selected.
                 *
                 * @param  array|mixed                        $columns
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function select($columns = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->select($columns);
                }

                /**
                 * Add a new "raw" select expression to the query.
                 *
                 * @param  string                             $expression
                 * @param  array                              $bindings
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function selectRaw($expression, $bindings = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->selectRaw($expression, $bindings);
                }

                /**
                 * Add a subselect expression to the query.
                 *
                 * @param  \Closure|\Illuminate\Database\Query\Builder|string $query
                 * @param  string                                             $as
                 * @throws \InvalidArgumentException
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function selectSub($query, $as)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->selectSub($query, $as);
                }

                /**
                 * Set the bindings on the query builder.
                 *
                 * @param  array                              $bindings
                 * @param  string                             $type
                 * @throws \InvalidArgumentException
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function setBindings($bindings, $type = 'where')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->setBindings($bindings, $type);
                }

                /**
                 * Set the relationships being eagerly loaded.
                 *
                 * @param  array                                        $eagerLoad
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function setEagerLoads($eagerLoad)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->setEagerLoads($eagerLoad);
                }

                /**
                 * Set a model instance for the model being queried.
                 *
                 * @param  \Illuminate\Database\Eloquent\Model          $model
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function setModel($model)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->setModel($model);
                }

                /**
                 * Set the underlying query builder instance.
                 *
                 * @param  \Illuminate\Database\Query\Builder           $query
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function setQuery($query)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->setQuery($query);
                }

                /**
                 * Share lock the selected rows in the table.
                 *
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function sharedLock()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->sharedLock();
                }

                /**
                 * Paginate the given query into a simple paginator.
                 *
                 * @param  int|null                                   $perPage
                 * @param  array                                      $columns
                 * @param  string                                     $pageName
                 * @param  int|null                                   $page
                 * @return \Illuminate\Contracts\Pagination\Paginator
                 * @static
                 */
                public static function simplePaginate($perPage = null, $columns = [], $pageName = 'page', $page = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->simplePaginate($perPage, $columns, $pageName, $page);
                }

                /**
                 * Alias to set the "offset" value of the query.
                 *
                 * @param  int                                $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function skip($value)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->skip($value);
                }

                /**
                 * Execute the query and get the first result if it's the sole matching record.
                 *
                 * @param  array|string                                         $columns
                 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
                 * @throws \Illuminate\Database\MultipleRecordsFoundException
                 * @return \Illuminate\Database\Eloquent\Model
                 * @static
                 */
                public static function sole($columns = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->sole($columns);
                }

                /**
                 * Retrieve the sum of the values of a given column.
                 *
                 * @param  string $column
                 * @return mixed
                 * @static
                 */
                public static function sum($column)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->sum($column);
                }

                /**
                 * Alias to set the "limit" value of the query.
                 *
                 * @param  int                                $value
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function take($value)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->take($value);
                }

                /**
                 * Pass the query to a given callback.
                 *
                 * @param  callable                                     $callback
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function tap($callback)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->tap($callback);
                }

                /**
                 * Get a base query builder instance.
                 *
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function toBase()
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->toBase();
                }

                /**
                 * Get the SQL representation of the query.
                 *
                 * @return string
                 * @static
                 */
                public static function toSql()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->toSql();
                }

                /**
                 * Run a truncate statement on the table.
                 *
                 * @return void
                 * @static
                 */
                public static function truncate()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    $instance->truncate();
                }

                /**
                 * Add a union statement to the query.
                 *
                 * @param  \Closure|\Illuminate\Database\Query\Builder $query
                 * @param  bool                                        $all
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function union($query, $all = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->union($query, $all);
                }

                /**
                 * Add a union all statement to the query.
                 *
                 * @param  \Closure|\Illuminate\Database\Query\Builder $query
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function unionAll($query)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->unionAll($query);
                }

                /**
                 * Apply the callback's query changes if the given "value" is false.
                 *
                 * @param  mixed         $value
                 * @param  callable      $callback
                 * @param  callable|null $default
                 * @return $this|mixed
                 * @static
                 */
                public static function unless($value, $callback, $default = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->unless($value, $callback, $default);
                }

                /**
                 * Create or update a record matching the attributes, and fill it with values.
                 *
                 * @param  array                                      $attributes
                 * @param  array                                      $values
                 * @return \Illuminate\Database\Eloquent\Model|static
                 * @static
                 */
                public static function updateOrCreate($attributes, $values = [])
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->updateOrCreate($attributes, $values);
                }

                /**
                 * Insert or update a record matching the attributes, and fill it with values.
                 *
                 * @param  array $attributes
                 * @param  array $values
                 * @return bool
                 * @static
                 */
                public static function updateOrInsert($attributes, $values = [])
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->updateOrInsert($attributes, $values);
                }

                /**
                 * Insert new records or update the existing ones.
                 *
                 * @param  array        $values
                 * @param  array|string $uniqueBy
                 * @param  array|null   $update
                 * @return int
                 * @static
                 */
                public static function upsert($values, $uniqueBy, $update = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->upsert($values, $uniqueBy, $update);
                }

                /**
                 * Use the write pdo for query.
                 *
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function useWritePdo()
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->useWritePdo();
                }

                /**
                 * Get a single column's value from the first result of a query.
                 *
                 * @param  \Illuminate\Database\Query\Expression|string $column
                 * @return mixed
                 * @static
                 */
                public static function value($column)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->value($column);
                }

                /**
                 * Apply the callback's query changes if the given "value" is true.
                 *
                 * @param  mixed         $value
                 * @param  callable      $callback
                 * @param  callable|null $default
                 * @return $this|mixed
                 * @static
                 */
                public static function when($value, $callback, $default = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->when($value, $callback, $default);
                }

                /**
                 * Add a basic where clause to the query.
                 *
                 * @param  array|\Closure|\Illuminate\Database\Query\Expression|string $column
                 * @param  mixed                                                       $operator
                 * @param  mixed                                                       $value
                 * @param  string                                                      $boolean
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function where($column, $operator = null, $value = null, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->where($column, $operator, $value, $boolean);
                }

                /**
                 * Add a where between statement to the query.
                 *
                 * @param  \Illuminate\Database\Query\Expression|string $column
                 * @param  array                                        $values
                 * @param  string                                       $boolean
                 * @param  bool                                         $not
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereBetween($column, $values, $boolean = 'and', $not = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereBetween($column, $values, $boolean, $not);
                }

                /**
                 * Add a where between statement using columns to the query.
                 *
                 * @param  string                             $column
                 * @param  array                              $values
                 * @param  string                             $boolean
                 * @param  bool                               $not
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereBetweenColumns($column, $values, $boolean = 'and', $not = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereBetweenColumns($column, $values, $boolean, $not);
                }

                /**
                 * Add a "where" clause comparing two columns to the query.
                 *
                 * @param  array|string                       $first
                 * @param  string|null                        $operator
                 * @param  string|null                        $second
                 * @param  string|null                        $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereColumn($first, $operator, $second, $boolean);
                }

                /**
                 * Add a "where date" statement to the query.
                 *
                 * @param  string                             $column
                 * @param  string                             $operator
                 * @param  \DateTimeInterface|string|null     $value
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereDate($column, $operator, $value = null, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereDate($column, $operator, $value, $boolean);
                }

                /**
                 * Add a "where day" statement to the query.
                 *
                 * @param  string                             $column
                 * @param  string                             $operator
                 * @param  \DateTimeInterface|string|null     $value
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereDay($column, $operator, $value = null, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereDay($column, $operator, $value, $boolean);
                }

                /**
                 * Add a relationship count / exists condition to the query with where clauses.
                 *
                 * @param  string                                       $relation
                 * @param  \Closure|null                                $callback
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function whereDoesntHave($relation, $callback = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->whereDoesntHave($relation, $callback);
                }

                /**
                 * Add a polymorphic relationship count / exists condition to the query with where clauses.
                 *
                 * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string $relation
                 * @param  array|string                                           $types
                 * @param  \Closure|null                                          $callback
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function whereDoesntHaveMorph($relation, $types, $callback = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->whereDoesntHaveMorph($relation, $types, $callback);
                }

                /**
                 * Add an exists clause to the query.
                 *
                 * @param  \Closure                           $callback
                 * @param  string                             $boolean
                 * @param  bool                               $not
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereExists($callback, $boolean = 'and', $not = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereExists($callback, $boolean, $not);
                }

                /**
                 * Add a relationship count / exists condition to the query with where clauses.
                 *
                 * @param  string                                       $relation
                 * @param  \Closure|null                                $callback
                 * @param  string                                       $operator
                 * @param  int                                          $count
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function whereHas($relation, $callback = null, $operator = '>=', $count = 1)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->whereHas($relation, $callback, $operator, $count);
                }

                /**
                 * Add a polymorphic relationship count / exists condition to the query with where clauses.
                 *
                 * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string $relation
                 * @param  array|string                                           $types
                 * @param  \Closure|null                                          $callback
                 * @param  string                                                 $operator
                 * @param  int                                                    $count
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function whereHasMorph($relation, $types, $callback = null, $operator = '>=', $count = 1)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->whereHasMorph($relation, $types, $callback, $operator, $count);
                }

                /**
                 * Add a "where in" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  mixed                              $values
                 * @param  string                             $boolean
                 * @param  bool                               $not
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereIn($column, $values, $boolean = 'and', $not = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereIn($column, $values, $boolean, $not);
                }

                /**
                 * Add a "where in raw" clause for integer values to the query.
                 *
                 * @param  string                                        $column
                 * @param  array|\Illuminate\Contracts\Support\Arrayable $values
                 * @param  string                                        $boolean
                 * @param  bool                                          $not
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereIntegerInRaw($column, $values, $boolean, $not);
                }

                /**
                 * Add a "where not in raw" clause for integer values to the query.
                 *
                 * @param  string                                        $column
                 * @param  array|\Illuminate\Contracts\Support\Arrayable $values
                 * @param  string                                        $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereIntegerNotInRaw($column, $values, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereIntegerNotInRaw($column, $values, $boolean);
                }

                /**
                 * Add a "where JSON contains" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  mixed                              $value
                 * @param  string                             $boolean
                 * @param  bool                               $not
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereJsonContains($column, $value, $boolean = 'and', $not = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereJsonContains($column, $value, $boolean, $not);
                }

                /**
                 * Add a "where JSON not contains" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  mixed                              $value
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereJsonDoesntContain($column, $value, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereJsonDoesntContain($column, $value, $boolean);
                }

                /**
                 * Add a "where JSON length" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  mixed                              $operator
                 * @param  mixed                              $value
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereJsonLength($column, $operator, $value = null, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereJsonLength($column, $operator, $value, $boolean);
                }

                /**
                 * Add a where clause on the primary key to the query.
                 *
                 * @param  mixed                                        $id
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function whereKey($id)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->whereKey($id);
                }

                /**
                 * Add a where clause on the primary key to the query.
                 *
                 * @param  mixed                                        $id
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function whereKeyNot($id)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->whereKeyNot($id);
                }

                /**
                 * Add a "where month" statement to the query.
                 *
                 * @param  string                             $column
                 * @param  string                             $operator
                 * @param  \DateTimeInterface|string|null     $value
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereMonth($column, $operator, $value = null, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereMonth($column, $operator, $value, $boolean);
                }

                /**
                 * Add a nested where statement to the query.
                 *
                 * @param  \Closure                           $callback
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereNested($callback, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereNested($callback, $boolean);
                }

                /**
                 * Add a where not between statement to the query.
                 *
                 * @param  string                             $column
                 * @param  array                              $values
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereNotBetween($column, $values, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereNotBetween($column, $values, $boolean);
                }

                /**
                 * Add a where not between statement using columns to the query.
                 *
                 * @param  string                             $column
                 * @param  array                              $values
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereNotBetweenColumns($column, $values, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereNotBetweenColumns($column, $values, $boolean);
                }

                /**
                 * Add a where not exists clause to the query.
                 *
                 * @param  \Closure                           $callback
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereNotExists($callback, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereNotExists($callback, $boolean);
                }

                /**
                 * Add a "where not in" clause to the query.
                 *
                 * @param  string                             $column
                 * @param  mixed                              $values
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereNotIn($column, $values, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereNotIn($column, $values, $boolean);
                }

                /**
                 * Add a "where not null" clause to the query.
                 *
                 * @param  array|string                       $columns
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereNotNull($columns, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereNotNull($columns, $boolean);
                }

                /**
                 * Add a "where null" clause to the query.
                 *
                 * @param  array|string                       $columns
                 * @param  string                             $boolean
                 * @param  bool                               $not
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereNull($columns, $boolean = 'and', $not = false)
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereNull($columns, $boolean, $not);
                }

                /**
                 * Add a raw where clause to the query.
                 *
                 * @param  string                             $sql
                 * @param  mixed                              $bindings
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereRaw($sql, $bindings = [], $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereRaw($sql, $bindings, $boolean);
                }

                /**
                 * Adds a where condition using row values.
                 *
                 * @param  array                              $columns
                 * @param  string                             $operator
                 * @param  array                              $values
                 * @param  string                             $boolean
                 * @throws \InvalidArgumentException
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereRowValues($columns, $operator, $values, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereRowValues($columns, $operator, $values, $boolean);
                }

                /**
                 * Add a "where time" statement to the query.
                 *
                 * @param  string                             $column
                 * @param  string                             $operator
                 * @param  \DateTimeInterface|string|null     $value
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereTime($column, $operator, $value = null, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereTime($column, $operator, $value, $boolean);
                }

                /**
                 * Add a "where year" statement to the query.
                 *
                 * @param  string                             $column
                 * @param  string                             $operator
                 * @param  \DateTimeInterface|int|string|null $value
                 * @param  string                             $boolean
                 * @return \Illuminate\Database\Query\Builder
                 * @static
                 */
                public static function whereYear($column, $operator, $value = null, $boolean = 'and')
                {
                    // @var \Illuminate\Database\Query\Builder $instance
                    return $instance->whereYear($column, $operator, $value, $boolean);
                }

                /**
                 * Add subselect queries to include an aggregate value for a relationship.
                 *
                 * @param  mixed                                        $relations
                 * @param  string                                       $column
                 * @param  string                                       $function
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function withAggregate($relations, $column, $function = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->withAggregate($relations, $column, $function);
                }

                /**
                 * Add subselect queries to include the average of the relation's column.
                 *
                 * @param  array|string                                 $relation
                 * @param  string                                       $column
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function withAvg($relation, $column)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->withAvg($relation, $column);
                }

                /**
                 * Apply query-time casts to the model instance.
                 *
                 * @param  array                                        $casts
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function withCasts($casts)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->withCasts($casts);
                }

                /**
                 * Add subselect queries to count the relations.
                 *
                 * @param  mixed                                        $relations
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function withCount($relations)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->withCount($relations);
                }

                /**
                 * Register a new global scope.
                 *
                 * @param  string                                       $identifier
                 * @param  \Closure|\Illuminate\Database\Eloquent\Scope $scope
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function withGlobalScope($identifier, $scope)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->withGlobalScope($identifier, $scope);
                }

                /**
                 * Add subselect queries to include the max of the relation's column.
                 *
                 * @param  array|string                                 $relation
                 * @param  string                                       $column
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function withMax($relation, $column)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->withMax($relation, $column);
                }

                /**
                 * Add subselect queries to include the min of the relation's column.
                 *
                 * @param  array|string                                 $relation
                 * @param  string                                       $column
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function withMin($relation, $column)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->withMin($relation, $column);
                }

                /**
                 * Prevent the specified relations from being eager loaded.
                 *
                 * @param  mixed                                        $relations
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function without($relations)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->without($relations);
                }

                /**
                 * Remove a registered global scope.
                 *
                 * @param  \Illuminate\Database\Eloquent\Scope|string   $scope
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function withoutGlobalScope($scope)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->withoutGlobalScope($scope);
                }

                /**
                 * Remove all or passed registered global scopes.
                 *
                 * @param  array|null                                   $scopes
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function withoutGlobalScopes($scopes = null)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->withoutGlobalScopes($scopes);
                }

                /**
                 * Add subselect queries to include the sum of the relation's column.
                 *
                 * @param  array|string                                 $relation
                 * @param  string                                       $column
                 * @return \Illuminate\Database\Eloquent\Builder|static
                 * @static
                 */
                public static function withSum($relation, $column)
                {
                    // @var \Illuminate\Database\Eloquent\Builder $instance
                    return $instance->withSum($relation, $column);
                }
            }
            class Event extends \Illuminate\Support\Facades\Event
            {
            }
            class File extends \Illuminate\Support\Facades\File
            {
            }
            class Gate extends \Illuminate\Support\Facades\Gate
            {
            }
            class Hash extends \Illuminate\Support\Facades\Hash
            {
            }
            class Lang extends \Illuminate\Support\Facades\Lang
            {
            }
            class Log extends \Illuminate\Support\Facades\Log
            {
            }
            class Mail extends \Illuminate\Support\Facades\Mail
            {
            }
            class Notification extends \Illuminate\Support\Facades\Notification
            {
            }
            class Password extends \Illuminate\Support\Facades\Password
            {
            }
            class PDFMerger extends \GrofGraf\LaravelPDFMerger\Facades\PDFMergerFacade
            {
            }
            class Queue extends \Illuminate\Support\Facades\Queue
            {
            }
            class Redirect extends \Illuminate\Support\Facades\Redirect
            {
            }
            class RedisManager extends \Illuminate\Support\Facades\Redis
            {
            }
            class Request extends \Illuminate\Support\Facades\Request
            {
            }
            class Response extends \Illuminate\Support\Facades\Response
            {
            }
            class Route extends \Illuminate\Support\Facades\Route
            {
            }
            class Schema extends \Illuminate\Support\Facades\Schema
            {
            }
            class Session extends \Illuminate\Support\Facades\Session
            {
            }
            class Storage extends \Illuminate\Support\Facades\Storage
            {
            }
            class URL extends \Illuminate\Support\Facades\URL
            {
            }
            class Validator extends \Illuminate\Support\Facades\Validator
            {
            }
            class View extends \Illuminate\Support\Facades\View
            {
            }
            class ShortURL extends \AshAllenDesign\ShortURL\Facades\ShortURL
            {
            }
            class Cerberus extends \Michalisantoniou6\Cerberus\CerberusFacade
            {
            }
            class PdfMerger extends \LynX39\LaraPdfMerger\Facades\PdfMerger
            {
            }
            class Laraload extends \DarkGhostHunter\Laraload\Facades\Laraload
            {
            }
            class Flare extends \Facade\Ignition\Facades\Flare
            {
            }
            class Image extends \Intervention\Image\Facades\Image
            {
            }
            class Agent extends \Jenssegers\Agent\Facades\Agent
            {
            }
            class Slack extends \jeremykenedy\Slack\Laravel\Facade
            {
            }
            class Nova extends \Laravel\Nova\Nova
            {
            }
            class Form extends \Collective\Html\FormFacade
            {
            }
            class Html extends \Collective\Html\HtmlFacade
            {
            }
            class Excel extends \Maatwebsite\Excel\Facades\Excel
            {
            }
            class Debouncer extends \Mpbarlow\LaravelQueueDebouncer\Facade\Debouncer
            {
            }
            class ScoutApm extends \Scoutapm\Laravel\Facades\ScoutApm
            {
            }
            class Sentry extends \Sentry\Laravel\Facade
            {
            }
            class DataTables extends \Yajra\DataTables\Facades\DataTables
            {
            }
}
