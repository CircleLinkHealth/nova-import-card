<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Exceptions\InvalidArgumentException;
use App\Http\Requests\CreateLegacyBhiConsentDecision;
use App\Note;
use CircleLinkHealth\Customer\Entities\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LegacyBhiConsentController extends Controller
{
    public function store(CreateLegacyBhiConsentDecision $request, $practiceId, $patientId)
    {
        if (1 === (int) $request->input('decision')) {
            $note = $this->createNote($patientId, Patient::BHI_CONSENT_NOTE_TYPE);
        } elseif (0 === (int) $request->input('decision')) {
            $note = $this->createNote($patientId, Patient::BHI_REJECTION_NOTE_TYPE);
        } elseif (2 === (int) $request->input('decision')) {
            $this->storeNotNowResponse($patientId);
        }

        return redirect()->back();
    }

    private function createNote($patientId, $type)
    {
        if ( ! in_array($type, [Patient::BHI_REJECTION_NOTE_TYPE, Patient::BHI_CONSENT_NOTE_TYPE])) {
            throw new InvalidArgumentException("`${type}` is not a valid type for a legacy BHI consent note type");
        }

        $body = Patient::BHI_CONSENT_NOTE_TYPE == $type
            ? 'The patient consented to receiving BHI services.'
            : 'The patient did not consent to receiving BHI services.';

        return Note::create([
            'patient_id'   => $patientId,
            'author_id'    => 948, //clh patient support
            'body'         => $body,
            'type'         => $type,
            'performed_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * When the User clicks `Not Now` we want to hide the banner for 24 hours.
     *
     * @param $patientId
     */
    private function storeNotNowResponse($patientId)
    {
        $key     = auth()->user()->getLegacyBhiNursePatientCacheKey($patientId);
        $minutes = intval(Carbon::now()->secondsUntilEndOfDay() / 60);

        Cache::put($key, true, $minutes);
    }
}
