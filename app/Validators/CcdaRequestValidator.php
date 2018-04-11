<?php

namespace App\Validators;

use \Prettus\Validator\Contracts\ValidatorInterface;
use \Prettus\Validator\LaravelValidator;

class CcdaRequestValidator extends LaravelValidator
{

    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'patient_id' => 'required',
            'practice_id' => 'required',
            'vendor' => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [],
    ];
}
