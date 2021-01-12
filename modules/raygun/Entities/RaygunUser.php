<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Raygun\Entities;

use CircleLinkHealth\Customer\Entities\User;

class RaygunUser
{
    /**
     * @return array
     */
    public function __invoke()
    {
        /** @var User $user */
        $user = auth()->user();

        return
            [
                'identifier'  => $user->id,
                'isAnonymous' => false,
                'email'       => $user->email,
                'firstName'   => $user->first_name,
                'fullName'    => $user->display_name,
            ]
        ;
    }
}
