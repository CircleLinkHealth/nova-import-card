<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use App\Contracts\ScoutSearch;

abstract class BaseScoutSearch implements ScoutSearch
{
    /**
     * Tag all searches with this so we can easily flush them from the cache.
     *
     * @var string
     */
    const SCOUT_SEARCHES_CACHE_TAG = 'scout_searches';
    /**
     * Two weeks in minutes.
     *
     * @var int
     */
    const TWO_WEEKS = 21600;

    /**
     * The time in minutes to cache the result of this search for.
     *
     * @var int
     */
    protected $duration = self::TWO_WEEKS;

    /**
     * The name of thi search.
     *
     * @var string
     */
    protected $name;

    /**
     * How long to store in cache for.
     *
     * @return int
     */
    public function duration(): int
    {
        return $this->duration;
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

    /**
     * The name of this search. Will be used in cache keys, tags.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
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
            self::SCOUT_SEARCHES_CACHE_TAG,
        ];
    }
}
