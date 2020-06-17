<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use CircleLinkHealth\Core\Notifications\Channels\CustomMailChannel;
use CircleLinkHealth\Core\Notifications\Channels\CustomTwilioChannel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Auth;
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
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /** @var ChannelManager $cm */
        $cm = $this->app->make(ChannelManager::class);
        $cm->extend('twilio', function (Application $app) {
            return $app->make(CustomTwilioChannel::class);
        });
        $cm->extend('mail', function (Application $app) {
            return $app->make(CustomMailChannel::class);
        });

        Auth::provider('awv', function ($app, array $config) {
            return new AwvUserProvider($app['hash'], $config['model']);
        });
    }
}
