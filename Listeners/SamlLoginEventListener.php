<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use CircleLinkHealth\SamlSp\Entities\SamlUser;
use Illuminate\Support\Facades\Auth;

class SamlLoginEventListener
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
    public function handle(Saml2LoginEvent $event)
    {
        /** @var string $idp */
        $idp       = $event->getSaml2Idp();
        $idpUserId = $event->getSaml2User()->getAttributesWithFriendlyName()['uid'];
        $samlUser  = SamlUser::with('cpmUser')
            ->where('idp', '=', $idp)
            ->where('idp_user_id', '=', $idpUserId)
            ->first();

        if ($samlUser && $samlUser->cpmUser) {
            Auth::login($samlUser->cpmUser);
        }
    }
}
