<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use CircleLinkHealth\Customer\Entities\Location;
use Laravel\Scout\Builder;

class LocationByName extends BaseScoutSearch
{
    /**
     * The eloquent query for performing the search.
     */
    public function query(string $term): Builder
    {
        return Location::search($term);
    }
}
