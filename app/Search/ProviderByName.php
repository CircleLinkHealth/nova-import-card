<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use CircleLinkHealth\Customer\Entities\User;

class ProviderByName extends BaseScoutSearch
{
    /**
     * The name of this search.
     */
    protected $name = 'search_provider_by_name';

    /**
     * The eloquent query for performing the search.
     *
     * @param string $term
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function query(string $term)
    {
        return User::search($term)->first();
    }
}
