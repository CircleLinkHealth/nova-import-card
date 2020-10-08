<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Saml2User;
use CircleLinkHealth\SamlSp\Entities\SamlResponseAttributes;
use CircleLinkHealth\SamlSp\Entities\SamlUser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'user_id'      => 'name_id',
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
            throw new AuthenticationException('Could not find cpm user mapping', [], '/saml2/not-auth');
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
                $ehr = DB::table('ehrs')
                    ->where('name', '=', $idp)
                    ->first();

                if ($ehr) {
                    $targetPatient = DB::table('target_patients')
                        ->where('ehr_id', '=', $ehr->id)
                        ->where('ehr_patient_id', '=', $idpAttributes->patientId)
                        ->first();

                    if ($targetPatient) {
                        $cpmPatientUrl = str_replace('{USER_ID}', $targetPatient->user_id, $cpmPatientUrl);
                        $cpmUrl .= $cpmPatientUrl;
                    } else {
                        Log::warning("Could not find cpm patient with id[$idpAttributes->patientId] from $idp");
                    }
                } else {
                    Log::warning("Could not found $idp in ehrs table");
                }
            }

            /** @var Request $request */
            $request = app('request');
            $request->merge([
                'RelayState' => $cpmUrl,
            ]);
        }
    }

    private function getSamlAttributes(string $idpName, Saml2User $saml2User): SamlResponseAttributes
    {
        if ( ! isset(self::IDP_KEY_MAPPINGS[$idpName])) {
            Log::warning("Could not parse from idp[$idpName]. Attributes: ".json_encode($saml2User->getAttributes()));
            throw new AuthenticationException("Could not parse attributes from idp[$idpName]", [], '/saml2/not-auth');
        }

        $mapping          = self::IDP_KEY_MAPPINGS[$idpName];
        $attributes       = $mapping['use_friendly'] ? $saml2User->getAttributesWithFriendlyName() : $saml2User->getAttributes();
        $attributes       = array_merge($attributes, ['name_id' => $saml2User->getNameId()]);
        $userIdMapping    = $mapping['user_id'];
        $patientIdMapping = $mapping['patient_id'];
        if ( ! isset($attributes[$userIdMapping])) {
            Log::warning("Could not find user id[$userIdMapping] in attributes of Saml2User. Attributes: ".json_encode($attributes));
            throw new AuthenticationException('Could not find user from saml attributes', [], '/saml2/not-auth');
        }

        return new SamlResponseAttributes($attributes[$userIdMapping], $attributes[$patientIdMapping] ?? null);
    }
}
