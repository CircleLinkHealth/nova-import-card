<?php

namespace App\Validators;

use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class UserValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'user_email' => 'required|unique:users|email',
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [],
    ];
}
