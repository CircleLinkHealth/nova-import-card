<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Validators;

use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class PracticeValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'name' => 'required|unique:practices,name',
        ],
        ValidatorInterface::RULE_UPDATE => [],
    ];
}
