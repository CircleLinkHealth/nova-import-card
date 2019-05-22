<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use CircleLinkHealth\Customer\Entities\User;

class ProviderByName
{
    /**
     * The name of this search.
     */
    const SEARCH_NAME = 'search_provider_by_name';

    /**
     * Two weeks in minutes.
     */
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
     * How long to store in cache for.
     *
     * @return int
     */
    private function duration(): int
    {
        return self::TWO_WEEKS;
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
        return \Cache::tags($this->tags())
            ->remember(
                self::key($term),
                $this->duration(),
                function () use ($term) {
                    return $this->query($term);
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
        return "{$this->name()}_$term";
    }

    /**
     * The name of this search. Will be used in cache keys, tags.
     *
     * @return string
     */
    private function name(): string
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
    private function query(string $term)
    {
        return User::search($term)->first();
    }

    private function tags(): array
    {
        return [
            $this->name(),
            'scout_searches',
        ];
    }
}
