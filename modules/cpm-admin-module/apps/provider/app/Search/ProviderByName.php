<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use CircleLinkHealth\Customer\Entities\User;
use Laravel\Scout\Builder;

class ProviderByName extends BaseScoutSearch
{
    /**
     * The eloquent query for performing the search.
     */
    public function query(string $term): Builder
    {
        return User::search($term);
    }
}
