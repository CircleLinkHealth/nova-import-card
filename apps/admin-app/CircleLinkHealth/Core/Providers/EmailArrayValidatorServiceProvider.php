<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class EmailArrayValidatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Validator::extend('email_array', function ($attribute, $value, $parameters, $validator) {
            $value = str_replace(' ', '', $value);
            $array = explode(',', $value);
            foreach ($array as $email) { //loop over values
                $email_to_validate['alert_email'][] = $email;
            }
            $rules = ['alert_email.*' => 'email'];
            $messages = [
                'alert_email.*' => trans('validation.email_array'),
            ];
            $validator = Validator::make($email_to_validate, $rules, $messages);
            if ($validator->passes()) {
                return true;
            }

            return false;
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
