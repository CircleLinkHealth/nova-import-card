<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/26/19
 * Time: 9:05 PM
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