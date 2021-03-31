<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Listeners;

use CircleLinkHealth\SamlSp\Entities\SamlUser;
use CircleLinkHealth\SmartOnFhirSso\Events\LoginEvent;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoginEventListener
{
    /**
     * @throws AuthenticationException
     */
    public function handle(LoginEvent $event)
    {
        /** @var SamlUser $samlUser */
        $samlUser = SamlUser::with('cpmUser')
            ->where('idp', '=', $event->platform)
            ->where('idp_user_id', '=', $event->ehrUserId)
            ->first();

        if ( ! $samlUser || ! $samlUser->cpmUser) {
            Log::warning("Could not find ehr user[$event->ehrUserId] to cpm user mapping from epic");

            throw new AuthenticationException('Could not find cpm user mapping', [], route('login'));
        }

        if ($samlUser->cpmUser->isParticipant()) {
            Log::warning("Will not authenticate ehr user[$event->ehrUserId] | cpm user[$samlUser->cpm_user_id] because they are a patient");

            throw new AuthenticationException('Will not authenticate patient into CPM from epic', [], route('login'));
        }

        Auth::login($samlUser->cpmUser);

        $patientRedirectUrl = $this->getPatientRedirectUrl($event);
        if (empty($patientRedirectUrl)) {
            Log::warning('Could not get a url to redirect to patient from Epic SSO');
            $patientRedirectUrl = '/';
        }

        session()->put('url.intended', $patientRedirectUrl);
    }

    private function getPatientRedirectUrl(LoginEvent $event): ?string
    {
        $ehr = DB::table('ehrs')
            ->where('name', '=', $event->platform)
            ->first();

        if ( ! $ehr) {
            Log::warning('Could not found epic in ehrs table');

            return null;
        }

        $targetPatientId = optional(DB::table('target_patients')
            ->where('ehr_id', '=', $ehr->id)
            ->where('ehr_patient_id', '=', $event->ehrPatientId)
            ->first())->user_id;

        if ( ! $targetPatientId) {
            Log::warning("Could not find cpm patient with id[$event->ehrPatientId] from epic");

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
}
