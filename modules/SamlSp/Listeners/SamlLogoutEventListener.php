<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Listeners;

use Aacotroneo\Saml2\Events\Saml2LogoutEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SamlLogoutEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Saml2LogoutEvent $event)
    {
        Auth::logout();
        Session::save();
    }
}
