<?php

namespace App\CLH\Auth;


use App\User;
use Illuminate\Auth\Guard;

class CustomGuard extends Guard
{
    public function impersonate()
    {
        if ( $id = session( 'impersonateUserId' ) ) {
            parent::onceUsingId( $id );
            return true;
        }
        return false;
    }

    public function isImpersonating()
    {
        return $this->session->has( 'impersonateUserId' );
    }

    public function setUserToImpersonate(User $user)
    {
        session( ['impersonateUserId' => $user->ID] );
    }
}