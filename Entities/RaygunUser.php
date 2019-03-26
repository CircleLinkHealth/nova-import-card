<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/26/19
 * Time: 9:05 PM
 */

namespace CircleLinkHealth\Raygun\Entities;


class RaygunUser
{
    /**
     * @return array
     */
    public function __invoke()
    {
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