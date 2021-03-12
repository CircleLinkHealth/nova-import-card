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
        'testing' => [
            'use_friendly' => true,
            'user_id'      => 'uid',
            'patient_id'   => null,
        ],
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

        if ( ! $samlUser || ! $samlUser->cpmUser) {
            Log::warning("Could not find ehr user[$idpAttributes->userId] to cpm user mapping from $idp");

            throw new AuthenticationException('Could not find cpm user mapping', [], '/saml2/not-auth');
        }

        if ($samlUser->cpmUser->isParticipant()) {
            Log::warning("Will not authenticate ehr user[$idpAttributes->userId] | cpm user[$samlUser->cpm_user_id] because they are a patient");

            throw new AuthenticationException("Will not authenticate patient into CPM from $idp", [], '/saml2/not-auth');
        }

        Auth::login($samlUser->cpmUser);
        session()->put(self::SESSION_IDP_NAME_KEY, $idp);

        $patientRedirectUrl = $this->getPatientRedirectUrl($idp, $idpAttributes);
        if (empty($patientRedirectUrl)) {
            Log::warning("Could not get a url to redirect to patient from $idp SSO");

            return;
        }

        Log::warning("Redirecting to $patientRedirectUrl");

        /** @var Request $request */
        $request = app('request');
        $request->merge([
            'RelayState' => $patientRedirectUrl,
        ]);
    }

    private function getPatientRedirectUrl(string $idp, SamlResponseAttributes $idpAttributes): ?string
    {
        if (empty($idpAttributes->patientId)) {
            $stringified = json_encode($idpAttributes);
            Log::warning("idpAttributes does not have patient id: $stringified");

            return null;
        }

        $ehr = DB::table('ehrs')
            ->where('name', '=', $idp)
            ->first();

        if ( ! $ehr) {
            Log::warning("Could not found $idp in ehrs table");

            return null;
        }

        $targetPatientId = optional(DB::table('target_patients')
            ->where('ehr_id', '=', $ehr->id)
            ->where('ehr_patient_id', '=', $idpAttributes->patientId)
            ->first())->user_id;

        if ( ! $targetPatientId) {
            Log::warning("Could not find cpm patient with id[$idpAttributes->patientId] from $idp");

            return null;
        }

        if (isCpm()) {
            return route('patient.note.index', ['patientId' => $targetPatientId]);
        }

        $cpmUrl        = config('services.cpm.url', null);
        $cpmPatientUrl = config('services.cpm.url_patient', null);
        if (empty($cpmUrl) || empty($cpmPatientUrl)) {
            Log::debug('CPM URL or CPM Patient URL not found. Will not redirect.');

            return null;
        }
        $cpmPatientUrl = str_replace('{USER_ID}', $targetPatientId, $cpmPatientUrl);
        $cpmUrl .= $cpmPatientUrl;

        return $cpmUrl;
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

        $patientId = $attributes[$patientIdMapping] ?? null;
        if (is_array($patientId)) {
            $patientId = reset($patientId);
        }

        if ( ! $patientId) {
            $stringified = json_encode($attributes);
            Log::warning("SAML Attributes: $stringified");
        }

        return new SamlResponseAttributes($attributes[$userIdMapping], $patientId);
    }
}
