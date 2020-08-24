<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Traits;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

trait AssertsLocationsAndPracticesWithCollection
{
    public function assertLocations(User $actor, Collection $locationIds)
    {
        return $locationIds->intersect($actor->locations->pluck('id'))->count() > 1;
    }

    public function assertPractices(User $actor, Collection $practiceIds)
    {
        return $practiceIds->intersect($actor->practices->pluck('id'))->count() > 1;
    }
}
