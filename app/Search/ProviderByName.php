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
    const SEARCH_NAME = 'search_provider_by_name';

    /**
     * The name of this search. Will be used in cache keys, tags.
     *
     * @return string
     */
    public function name(): string
    {
        return self::SEARCH_NAME;
    }

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

    /**
     * Tags for this search.
     *
     * @return array
     */
    public function tags(): array
    {
        return [
            $this->name(),
            'scout_searches',
        ];
    }
}
