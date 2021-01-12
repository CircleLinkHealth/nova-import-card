<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use Laravel\Scout\Builder;

interface ScoutSearch
{
    /**
     * How long to store in cache for.
     */
    public function duration(): int;

    /**
     * Search using given term.
     *
     * @return mixed
     */
    public function find(string $term);

    /**
     * Essentially a static wrapper for find so that we can do `ProviderByName::first($term)`.
     *
     * @return mixed
     */
    public static function first(string $term);

    /**
     * @return string
     */
    public function key(string $term);

    /**
     * The name of this search. Will be used in cache keys, tags.
     */
    public function name(): string;

    /**
     * The eloquent query for performing the search.
     */
    public function query(string $term): Builder;

    /**
     * Tags for this search.
     */
    public function tags(): array;
}
