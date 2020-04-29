<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\Eligibility\Providers;

use App\Http\Controllers\Enrollment\Auth\EnrollmentAuthentication;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class AutoEnrollmentLoginProvider extends EloquentUserProvider
{
    use EnrollmentAuthentication;

    /**
     * AutoEnrollmentLoginProvider constructor.
     * @param $model
     */
    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    public function retrieveByCredentials(array $credentials)
    {
        $x = 1;
    }
}
