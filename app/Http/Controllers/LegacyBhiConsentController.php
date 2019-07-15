<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Exceptions\InvalidArgumentException;
use App\Http\Requests\CreateLegacyBhiConsentDecision;
use App\Note;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
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
        $consenderName = auth()->user()->display_name;

        $body = Patient::BHI_CONSENT_NOTE_TYPE == $type
            ? "The patient consented to receiving BHI services. \n 'Consented action taken by: $consenderName'"
            : "The patient did not consent to receiving BHI services. \n 'Not consented action taken by: $consenderName'";

        return Note::create([
            'patient_id'   => $patientId,
            'author_id'    => 948, //This user is CLH patient support
            'body'         => $body,
            'type'         => $type,
            'performed_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * When the User clicks `Not Now` we want to hide the banner till next scheduled call.
     * If Scheduled call is null will show banner & flag again tomorrow.
     *
     * @param $patientId
     */
    private function storeNotNowResponse($patientId)
    {
        $now                   = Carbon::now();
        $tomorrow              = $now->copy()->addHours(24);
        $nextScheduledCallDate = auth()->user()->patientNextScheduledCallDate($patientId);
        $key                   = auth()->user()->getLegacyBhiNursePatientCacheKey($patientId);

        $seconds = null !== $nextScheduledCallDate
            ? Carbon::parse($nextScheduledCallDate)->diffInSeconds($now)
            : $tomorrow->diffInSeconds($now);

        Cache::put($key, true, $seconds);
    }
}
