<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use CircleLinkHealth\Customer\Entities\Location;

class LocationByName extends BaseScoutSearch
{
    /**
     * The eloquent query for performing the search.
     *
     * @param string $term
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function query(string $term)
    {
        return Location::search($term)->first();
    }
}
