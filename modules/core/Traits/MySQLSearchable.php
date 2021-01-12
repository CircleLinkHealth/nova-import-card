<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Traits;

/**
 * Trait MySQLSearchable.
 */
trait MySQLSearchable
{
    /**
     * Available full-text modes for MySQL.
     *
     * @var array
     */
    protected $validModes = [
        'BOOLEAN',
        'NATURAL LANGUAGE',
    ];

    /**
     * Scope a query that matches a full text search of term.
     * Can be built upon.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $mode
     * @param bool                                  $shouldRequireAll
     * @param bool                                  $shouldRequireIntegers
     * @param mixed                                 $shouldIncludeRelevanceScore
     *
     * @throws \Exception
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMySQLSearch(
        $query,
        array $columns,
        string $term,
        $mode = 'BOOLEAN',
        $shouldRequireAll = true,
        $shouldRequireIntegers = true,
        $shouldIncludeRelevanceScore = false
    ) {
        if ( ! $this->validateMode($mode)) {
            throw new \Exception("Invalid MySQL full-text search mode: {$mode}");
        }

        $columns = implode(',', $columns);

        if ($shouldIncludeRelevanceScore) {
            $query->selectRaw(
                "*, MATCH ({$columns}) AGAINST (? IN {$mode} MODE)*100 as relevance_score",
                [$this->fullTextWildcards($term, $shouldRequireAll, $shouldRequireIntegers)]
            );
        }

        $query->whereRaw(
            "MATCH ({$columns}) AGAINST (? IN {$mode} MODE)",
            $this->fullTextWildcards($term, $shouldRequireAll, $shouldRequireIntegers)
        );

        return $query;
    }

    /**
     * Replaces spaces with full text search wildcards.
     *
     * @param mixed $mode
     *
     * @return string
     */
    protected function fullTextWildcards(
        string $term,
        $mode,
        bool $shouldRequireAll = true,
        bool $shouldRequireIntegers = true
    ) {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term            = str_replace($reservedSymbols, '', $term);

        $words = explode(' ', $term);

        if ('BOOLEAN' === $mode) {
            foreach ($words as $key => $word) {
                /*
                 * applying + operator (required word) only big words
                 * because smaller ones are not indexed by mysql
                 */
                if (strlen($word) >= 3) {
                    if ($shouldRequireIntegers && ! $shouldRequireAll) {
                        if (is_numeric($word)) {
                            $word = '+'.$word;
                        }
                    }

                    if ($shouldRequireAll) {
                        $word = '+'.$word;
                    }
                    $words[$key] = $word.'*';
                }
            }
        }

        return implode(' ', $words);
    }

    private function validateMode($mode)
    {
        return in_array($mode, $this->validModes);
    }
}
