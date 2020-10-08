<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use CircleLinkHealth\SamlSp\Entities\SamlUser;
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
     * @return void
     */
    public function handle(Saml2LoginEvent $event)
    {
        /** @var string $idp */
        $idp        = $event->getSaml2Idp();
        $attributes = $event->getSaml2User()->getAttributesWithFriendlyName();
        if ( ! isset($attributes['uid'])) {
            Log::warning('Could not find uid in attributes of Saml2User');

            return;
        }

        $idpUserId = $attributes['uid'];

        /** @var SamlUser $samlUser */
        $samlUser = SamlUser::with('cpmUser')
            ->where('idp', '=', $idp)
            ->where('idp_user_id', '=', $idpUserId)
            ->first();

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
}
