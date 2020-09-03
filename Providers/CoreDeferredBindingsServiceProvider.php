<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Providers;

use CircleLinkHealth\Core\Entities\DatabaseNotification as CircleLinkDatabaseNotification;
use CircleLinkHealth\Core\Notifications\Channels\DatabaseChannel as CircleLinkDatabaseChannel;
use CircleLinkHealth\Core\Traits\HasDatabaseNotifications as CircleLinkHasDatabaseNotifications;
use CircleLinkHealth\Core\Traits\Notifiable as CircleLinkNotifiable;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Notifications\Channels\DatabaseChannel as LaravelDatabaseChannel;
use Illuminate\Notifications\DatabaseNotification as LaravelDatabaseNotification;
use Illuminate\Notifications\HasDatabaseNotifications as LaravelHasDatabaseNotifications;
use Illuminate\Notifications\Notifiable as LaravelNotifiable;
use Illuminate\Support\ServiceProvider;

class CoreDeferredBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            LaravelDatabaseChannel::class,
            LaravelHasDatabaseNotifications::class,
            LaravelNotifiable::class,
            LaravelDatabaseNotification::class,
            CircleLinkDatabaseChannel::class,
            CircleLinkHasDatabaseNotifications::class,
            CircleLinkNotifiable::class,
            CircleLinkDatabaseNotification::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->bind(LaravelDatabaseChannel::class, CircleLinkDatabaseChannel::class);
        $this->app->bind(LaravelHasDatabaseNotifications::class, CircleLinkHasDatabaseNotifications::class);
        $this->app->bind(LaravelNotifiable::class, CircleLinkNotifiable::class);
        $this->app->bind(LaravelDatabaseNotification::class, CircleLinkDatabaseNotification::class);
    }
}
