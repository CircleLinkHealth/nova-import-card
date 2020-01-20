<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use CircleLinkHealth\Customer\Entities\Practice;
use Laravel\Scout\Builder;

class PracticeByName extends BaseScoutSearch
{
    /**
     * The eloquent query for performing the search.
     */
    public function query(string $term): Builder
    {
        return Practice::search($term);
    }
}
