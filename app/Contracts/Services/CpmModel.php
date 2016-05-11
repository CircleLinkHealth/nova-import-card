<?php

namespace App\Contracts\Services;

use App\CarePlanTemplate;
use App\User;

interface CpmModel
{
    /**
     * Sync the User's values with the CpmModel's values.
     * NOTE: The User will only be related with the Ids passed in ONLY. All others will be erased, just like Laravel's
     *      sync() method.
     *
     * @param User $user
     * @param array $ids
     * @param int $page
     * @return mixed
     */
    public function syncWithUser(User $user, array $ids, $page);
}