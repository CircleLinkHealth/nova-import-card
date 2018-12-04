<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Validators;

use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class UserValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'email'      => 'required|email|unique:users,email',
            'first_name' => 'required',
            'last_name'  => 'required',
            'password'   => 'required|min:8',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'first_name' => 'required',
            'last_name'  => 'required',
        ],
    ];
}
