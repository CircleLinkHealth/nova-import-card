<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Saml2User;
use CircleLinkHealth\SamlSp\Entities\SamlUser;
use CircleLinkHealth\SamlSp\Exceptions\SamlAuthenticationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SamlLoginEventListener
{
    const SESSION_IDP_NAME_KEY = 'saml_idp_name';

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
     * @throws AuthenticationException
     * @return void
     */
    public function handle(Saml2LoginEvent $event)
    {
        /** @var string $idp */
        $idp       = $event->getSaml2Idp();
        $idpUserId = $this->getIdpUserId($idp, $event->getSaml2User());

        /** @var SamlUser $samlUser */
        $samlUser = SamlUser::with('cpmUser')
            ->where('idp', '=', $idp)
            ->where('idp_user_id', '=', $idpUserId)
            ->first();

        if ( ! $samlUser) {
            throw new SamlAuthenticationException('Could not find cpm user mapping');
        }

        if ($samlUser && $samlUser->cpmUser && ! $samlUser->cpmUser->isParticipant()) {
            Auth::login($samlUser->cpmUser);
            session()->put(self::SESSION_IDP_NAME_KEY, $idp);

            $cpmUrl = config('services.cpm.url', null);
            if ( ! empty($cpmUrl)) {
                /** @var Request $request */
                $request = app('request');
                $request->merge([
                    'RelayState' => config('services.cpm.url'),
                ]);
            }

            // if we have a Patient ID in the $attributes
            // $patientId = $attributes['patientId'];
            // /** @var Request $request */
            // $request = app('request');
            // $request->merge(['RelayState' => route('patient.note.index', ['patientId' => $patientId])]);
        }
    }

    private function getIdpUserId(string $idpName, Saml2User $saml2User)
    {
        switch ($idpName) {
            case 'samltest':
                $attributes = $saml2User->getAttributesWithFriendlyName();
                if ( ! isset($attributes['uid'])) {
                    Log::warning('Could not find uid in attributes of Saml2User. Attributes: '.implode(',', $attributes));

                    throw new SamlAuthenticationException('Could not find user from saml attributes');
                }

                return $attributes['uid'];
            case 'athena':
                $attributes = $saml2User->getAttributes();
                if ( ! isset($attributes['uid'])) {
                    Log::warning('Could not find uid in attributes of Saml2User. Attributes: '.implode(',', $attributes));

                    throw new SamlAuthenticationException('Could not find user from saml attributes');
                }

                return $attributes['uid'];
            default:
                $attributes = $saml2User->getAttributes();
                Log::warning("Could not parse from idp[$idpName]. Attributes: ".implode(',', $attributes));
                throw new SamlAuthenticationException("Could not parse attributes from idp[$idpName]");
        }
    }
}
