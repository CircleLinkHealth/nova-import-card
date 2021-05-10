<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\LargePayloadSqsQueue;

use CircleLinkHealth\LargePayloadSqsQueue\Queue\Connectors\Connector;
use Illuminate\Support\ServiceProvider;

class LargePayloadSqsQueueServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register everything for the given manager.
     *
     * @param \Illuminate\Queue\QueueManager $manager
     *
     * @return void
     */
    public function extendManager($manager)
    {
        $this->registerConnectors($manager);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        // Queue is a deferred provider. We don't want to force resolution to provide
        // a new driver. Therefore, if the queue has already been resolved, extend
        // it now. Otherwise, extend the queue after it has been resolved.
        if ($app->bound('queue')) {
            $this->extendManager($app['queue']);
        } else {
            // "afterResolving" not introduced until 5.0. Before 5.0 uses "resolving".
            if (method_exists($app, 'afterResolving')) {
                $app->afterResolving('queue', function ($manager) {
                    $this->extendManager($manager);
                });
            } else {
                $app->resolving('queue', function ($manager) {
                    $this->extendManager($manager);
                });
            }
        }
    }

    /**
     * Register the connectors on the queue manager.
     *
     * @param \Illuminate\Queue\QueueManager $manager
     *
     * @return void
     */
    public function registerConnectors($manager)
    {
        $manager->extend('sqs-large-payload', function () {
            return new Connector();
        });
    }
}
