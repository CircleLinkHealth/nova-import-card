<?php

namespace App\CLH\Auth;


use App\User;
use Illuminate\Auth\Guard;

class CustomGuard extends Guard
{
    public function impersonate()
    {
        if ( $id = session( 'impersonate_member' ) ) {
            parent::onceUsingId( $id );
            return true;
        }
        return false;
    }

    public function isImpersonating()
    {
        return $this->session->has( 'impersonate_member' );
    }

    public function setUserToImpersonate(User $user)
    {
        session( ['impersonate_member' => $user->ID] );
    }
}