<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Saml2User;
use CircleLinkHealth\SamlSp\Entities\SamlResponseAttributes;
use CircleLinkHealth\SamlSp\Entities\SamlUser;
use CircleLinkHealth\SamlSp\Exceptions\SamlAuthenticationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SamlLoginEventListener
{
    const IDP_KEY_MAPPINGS = [
        'samltest' => [
            'use_friendly' => true,
            'user_id'      => 'uid',
            'patient_id'   => null,
        ],
        'athena' => [
            'use_friendly' => false,
            'user_id'      => 'subject',
            'patient_id'   => 'patientid',
        ],
    ];
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
        $idp           = $event->getSaml2Idp();
        $idpAttributes = $this->getSamlAttributes($idp, $event->getSaml2User());

        /** @var SamlUser $samlUser */
        $samlUser = SamlUser::with('cpmUser')
            ->where('idp', '=', $idp)
            ->where('idp_user_id', '=', $idpAttributes->userId)
            ->first();

        if ( ! $samlUser) {
            throw new SamlAuthenticationException('Could not find cpm user mapping');
        }

        if ($samlUser && $samlUser->cpmUser && ! $samlUser->cpmUser->isParticipant()) {
            Auth::login($samlUser->cpmUser);
            session()->put(self::SESSION_IDP_NAME_KEY, $idp);

            $cpmUrl = config('services.cpm.url', null);
            if (empty($cpmUrl)) {
                Log::debug('CPM URL not found. Will not redirect.');

                return;
            }

            $cpmPatientUrl = config('services.cpm.url_patient');
            if ( ! empty($idpAttributes->patientId) && ! empty($cpmPatientUrl)) {
                /** @var SamlUser $samlPatient */
                $samlPatient = SamlUser::where('idp', '=', $idp)
                    ->where('idp_user_id', '=', $idpAttributes->patientId)
                    ->first();
                if ($samlPatient) {
                    $cpmPatientUrl = str_replace('{USER_ID}', $samlPatient->cpm_user_id, $cpmPatientUrl);
                    $cpmUrl .= $cpmPatientUrl;
                }
            }

            /** @var Request $request */
            $request = app('request');
            $request->merge([
                'RelayState' => $cpmUrl,
            ]);

            // if we have a Patient ID in the $attributes
            // $patientId = $attributes['patientId'];
            // /** @var Request $request */
            // $request = app('request');
            // $request->merge(['RelayState' => route('patient.note.index', ['patientId' => $patientId])]);
        }
    }

    private function getSamlAttributes(string $idpName, Saml2User $saml2User): SamlResponseAttributes
    {
        if (isset(self::IDP_KEY_MAPPINGS[$idpName])) {
            $attributes = $saml2User->getAttributes();
            Log::warning("Could not parse from idp[$idpName]. Attributes: ".json_encode($attributes));
            throw new SamlAuthenticationException("Could not parse attributes from idp[$idpName]");
        }

        $mapping          = self::IDP_KEY_MAPPINGS[$idpName];
        $attributes       = $mapping['use_friendly'] ? $saml2User->getAttributesWithFriendlyName() : $saml2User->getAttributes();
        $userIdMapping    = $mapping['user_id'];
        $patientIdMapping = $mapping['patient_id'];
        if ( ! isset($attributes[$userIdMapping])) {
            Log::warning("Could not find user id[$userIdMapping] in attributes of Saml2User. Attributes: ".json_encode($attributes));

            throw new SamlAuthenticationException('Could not find user from saml attributes');
        }

        return new SamlResponseAttributes($attributes[$userIdMapping], $attributes[$patientIdMapping] ?? null);
    }
}
