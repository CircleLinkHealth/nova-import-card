<?php

namespace App\Validators;

use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class InviteValidator extends LaravelValidator
{

    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'inviter_id' => 'required',
            'role_id'    => 'required',
            'email'      => 'required',
            'subject'    => 'required',
            'message'    => 'required',
            'code'       => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [],
    ];
}
