<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use CircleLinkHealth\Customer\Contracts\SelfEnrollable;
use Illuminate\Database\Eloquent\Builder;

class CreateUsersFromEnrollees extends AbstractSelfEnrollableModelIterator
{
    public function action(SelfEnrollable $enrollableModel): void
    {
        // TODO: Implement action() method.
    }

    public function query(): Builder
    {
        // TODO: Implement query() method.
    }
}
