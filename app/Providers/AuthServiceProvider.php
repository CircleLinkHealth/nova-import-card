<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Hash;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //registering the 'api' auth driver
        //not really needed anywhere, except RaygunServiceProvider
        //it returns a fake user

        $this->app['auth']->viaRequest('api', function ($request) {
            $token = $request->input('cpm-token', null);
            if (empty($token) || ! Hash::check($this->getTokenString(), $token)) {
                return null;
            }
            $user = new User();
            $user->id = $token;
            $user->first_name = 'unknown';
            $user->display_name = 'unknown';
            $user->email = 'unknown@circlelinkhealth.com';

            return $user;
        });
    }

    private function getTokenString()
    {
        return config('app.key').Carbon::today()->toDateString();
    }
}
