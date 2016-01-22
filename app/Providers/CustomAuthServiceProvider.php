<?php namespace App\Providers;

use App\CLH\Auth\CustomUserProvider;
use App\CLH\Auth\CustomGuard;
use App\User;
use Illuminate\Auth\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\AuthManager;

class CustomAuthServiceProvider extends ServiceProvider {

	protected function registerAuthenticator()
	{
		$this->app->singleton('auth', function($app)
		{
			$app['auth.loaded'] = true;

			return new AuthManager($app);
		});

		$this->app['auth']->extend('custom', function ($app)
		{
			$model = $app['config']['auth.model'];

			$provider = new CustomUserProvider(new User);

            return new CustomGuard($provider, $this->app['session.store']);
        });

        $this->app->singleton('auth.driver', function($app)
		{
			return $app['auth']->driver();
		});
    }

}

