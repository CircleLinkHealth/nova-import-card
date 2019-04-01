<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Services;

use CircleLinkHealth\Customer\Entities\User;

interface CpmModel
{
    /**
     * Sync the User's values with the CpmModel's values.
     * NOTE: The User will only be related with the Ids passed in ONLY. All others will be erased, just like Laravel's
     *      sync() method.
     *
     * @param \CircleLinkHealth\Customer\Entities\User  $user
     * @param array $ids
     * @param int   $page
     * @param array $instructionsInput
     *
     * @return mixed
     */
    public function syncWithUser(User $user, array $ids, $page, array $instructionsInput);
}
