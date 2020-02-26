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
     * The prefix of the search's name.
     *
     * @var string
     */
    protected $prefix = 'search:';
    /**
     * Callback given to setWheres.
     *
     * @var callable
     */
    private $fn;
    /**
     * Add this to hash for uniqueness between practices.
     *
     * @var array
     */
    private $wheres;

    public function __construct()
    {
        if ( ! $this->name) {
            $this->generateSearchName();
        }
    }

    public function cache(callable $fn, string $term)
    {
        return \Cache::tags($this->tags())
            ->remember(
                self::key($term),
                $this->duration(),
                $fn
            );
    }

    /**
     * How long to store in cache for.
     */
    public function duration(): int
    {
        return $this->duration;
    }

    /**
     * Search using given term.
     *
     * @return mixed
     */
    public function find(string $term)
    {
        return $this->cache(
            function () use ($term) {
                return $this->decorateQuery($this->query($term))->first();
            },
            $term
        );
    }

    /**
     * Essentially a static wrapper for find so that we can do `ProviderByName::first($term)`.
     *
     * @return mixed
     */
    public static function first(string $term)
    {
        return (new static())->find($term);
    }

    /**
     * @return string
     */
    public function key(string $term)
    {
        if (empty($this->wheres)) {
            return sha1("{$this->name()}:$term");
        }

        return sha1("{$this->name()}:$term:".json_encode($this->wheres));
    }

    /**
     * The name of this search. Will be used in cache keys, tags.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return BaseScoutSearch
     */
    public function setWheres(array $wheres)
    {
        $this->wheres = $wheres;

        return $this;
    }

    /**
     * Tags for this search.
     */
    public function tags(): array
    {
        return [
            $this->name(),
            self::SCOUT_SEARCHES_CACHE_TAG,
        ];
    }

    private function decorateQuery(\Laravel\Scout\Builder $query)
    {
        if (empty($this->wheres)) {
            return $query;
        }

        foreach ($this->wheres as $key => $value) {
            $query->where($key, $value);
        }

        return $query;
    }

    private function generateSearchName()
    {
        $this->name = $this->prefix.get_class($this);
    }
}
