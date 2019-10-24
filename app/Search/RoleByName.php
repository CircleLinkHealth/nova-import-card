<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Search;

use CircleLinkHealth\Customer\Entities\Role;

class RoleByName extends BaseScoutSearch
{
    /**
     * The eloquent query for performing the search.
     *
     * @param string $term
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function query(string $term)
    {
        return Role::search($term)->first();
    }
}
