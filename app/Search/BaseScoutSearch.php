<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use App\Contracts\ScoutSearch;

abstract class BaseScoutSearch implements ScoutSearch
{
    /**
     * Two weeks in minutes.
     */
    const TWO_WEEKS = 21600;

    /**
     * How long to store in cache for.
     *
     * @return int
     */
    public function duration(): int
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
    public function find(string $term)
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
     * @param string $term
     *
     * @return string
     */
    public function key(string $term)
    {
        return "{$this->name()}_$term";
    }
}
