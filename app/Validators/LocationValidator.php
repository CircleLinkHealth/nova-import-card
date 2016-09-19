<?php

namespace App\Validators;

use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class LocationValidator extends LaravelValidator
{

    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'name' => 'required',
            'phone' => 'required',
            'address_line_1' => 'required',
            'address_line_2' => '',
            'city' => 'required',
            'state' => 'required',
            'timezone' => 'required',
            'postal_code' => 'required',
            'billing_code' => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [],
    ];
}
