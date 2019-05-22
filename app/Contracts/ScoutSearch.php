<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface ScoutSearch
{
    /**
     * How long to store in cache for.
     *
     * @return int
     */
    public function duration(): int;

    /**
     * Search using given term.
     *
     * @param string $term
     *
     * @return mixed
     */
    public function find(string $term);

    /**
     * Essentially a static wrapper for find so that we can do `ProviderByName::first($term)`.
     *
     * @param string $term
     *
     * @return mixed
     */
    public static function first(string $term);

    /**
     * @param string $term
     *
     * @return string
     */
    public function key(string $term);

    /**
     * The name of this search. Will be used in cache keys, tags.
     *
     * @return string
     */
    public function name(): string;

    /**
     * The eloquent query for performing the search.
     *
     * @param string $term
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function query(string $term);

    /**
     * Tags for this search.
     *
     * @return array
     */
    public function tags(): array;
}
