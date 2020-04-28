<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 01/07/2019
 * Time: 4:30 PM.
 */

namespace App\Providers;

use App\Services\TwilioClientService;
use Illuminate\Support\ServiceProvider;

class TwilioClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind(TwilioClientService::class, function () {
            return new TwilioClientService();
        });
    }
}
