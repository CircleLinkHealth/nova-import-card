<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class AutoEnrollmentLoginProvider extends EloquentUserProvider
{
//    Not in use ... shold get back and implement enrollment survey login
    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }
}
