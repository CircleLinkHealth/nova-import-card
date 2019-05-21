<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use CircleLinkHealth\Customer\Entities\User;

class ProviderByName
{
    const TWO_WEEKS = 21600;

    /**
     * Essentially a static wrapper for find so that we can do `ProviderByName::first($term)`.
     *
     * @param string $term
     *
     * @return mixed
     */
    public static function first(string $term)
    {
        return (new static())->find($term);
    }

    /**
     * Search using given term.
     *
     * @param string $term
     *
     * @return mixed
     */
    private function find(string $term)
    {
        return \Cache::remember(
            self::key($term),
            self::TWO_WEEKS,
            function () use ($term) {
                return User::search($term)->first();
            }
        );
    }

    /**
     * @param string $term
     *
     * @return string
     */
    private function key(string $term)
    {
        return "search_$term";
    }
}
