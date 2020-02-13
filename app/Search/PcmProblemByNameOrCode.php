<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use Laravel\Scout\Builder;

class PcmProblemByNameOrCode extends BaseScoutSearch
{
    /**
     * The eloquent query for performing the search.
     *
     * @param string $term
     *
     * @return Builder
     */
    public function query(string $term): Builder
    {
        return PcmProblem::search($term);
    }
}
