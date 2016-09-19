<?php

namespace App\Validators;

use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ProgramValidator extends LaravelValidator
{

    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'name' => 'required',
            'description' => '',
            'url' => 'unique',
        ],
        ValidatorInterface::RULE_UPDATE => [],
   ];
}
