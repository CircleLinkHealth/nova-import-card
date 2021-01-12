<?php

namespace App\Providers;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        //registering the 'api' auth driver
        //not really needed anywhere, except RaygunServiceProvider
        //it returns a fake user

        $this->app['auth']->viaRequest('api', function ($request) {
            $token = $request->input('cpm-token', null);
            if (empty($token) || ! Hash::check($this->getTokenString(), $token)) {
                return null;
            }
            $user               = new User();
            $user->id           = $token;
            $user->first_name   = 'unknown';
            $user->display_name = 'unknown';
            $user->email        = 'unknown@circlelinkhealth.com';

            return $user;
        });
    }

    private function getTokenString() {
        return config('app.key') . Carbon::today()->toDateString();
    }
}
