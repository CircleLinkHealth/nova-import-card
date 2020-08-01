<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\Customer\Entities\User;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/28/16
 * Time: 5:43 PM.
 */
class UserService
{
    /**
     * Get the User's first CarePlan, or relate the User to CLH's default CarePlan.
     *
     * @param \CircleLinkHealth\Customer\Entities\User|null $user
     *
     * @return \CircleLinkHealth\SharedModels\Entities\CarePlan
     */
    public function firstOrDefaultCarePlan(User $user)
    {
        return $user->carePlan()->firstOrCreate([
            'user_id'               => $user->id,
            'care_plan_template_id' => getDefaultCarePlanTemplate()->id,
        ]);
    }
}
