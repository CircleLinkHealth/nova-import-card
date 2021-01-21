<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Providers;

use CircleLinkHealth\TwilioIntegration\Models\TwilioCall;
use CircleLinkHealth\TwilioIntegration\Notifications\Channels\CustomTwilioChannel;
use CircleLinkHealth\TwilioIntegration\Observers\TwilioCallObserver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;

class TwilioIntegrationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        TwilioCall::observe(TwilioCallObserver::class);
    }

    public function register()
    {
        $cm = $this->app->make(ChannelManager::class);
        $cm->extend('twilio', function (Application $app) {
            return $app->make(CustomTwilioChannel::class);
        });

        $this->app->register(RouteServiceProvider::class);
        $this->app->register(TwilioClientServiceProvider::class);
    }
}
