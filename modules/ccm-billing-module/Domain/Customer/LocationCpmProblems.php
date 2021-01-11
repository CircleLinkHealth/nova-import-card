<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use CircleLinkHealth\CcmBilling\Contracts\LocationProblemServiceRepository;
use Illuminate\Database\Eloquent\Collection;

class LocationCpmProblems
{
    protected LocationProblemServiceRepository $repo;

    public function __construct(LocationProblemServiceRepository $repo)
    {
        $this->repo = $repo;
    }

    //todo: for location-problem-services feature
    public static function getCollection(int $locationId): Collection
    {
        return app(self::class)->get($locationId);
    }

    private function get(int $locationId): Collection
    {
        return $this->repo->problemsForLocation($locationId);
    }
}
