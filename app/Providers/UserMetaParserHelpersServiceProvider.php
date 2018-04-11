<?php namespace App\Providers;

use App\CLH\CCD\Importer\ParsingStrategies\Helpers\UserMetaParserHelpers;
use Illuminate\Support\ServiceProvider;

class UserMetaParserHelpersServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('userMetaParserHelpers', function () {
            return new UserMetaParserHelpers();
        });
    }
}
