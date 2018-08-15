<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidArgumentException;
use App\Http\Requests\CreateLegacyBhiConsentDecision;
use App\Note;
use App\Patient;

class LegacyBhiConsentController extends Controller
{
    public function store(CreateLegacyBhiConsentDecision $request, $practiceId, $patientId)
    {
        if ( ! ! $request->input('decision')) {
            $note = $this->createNote($patientId, Patient::BHI_CONSENT_NOTE_TYPE);
        } elseif ( ! ! ! $request->input('decision')) {
            $note = $this->createNote($patientId, Patient::BHI_REJECTION_NOTE_TYPE);
        }

        return redirect()->back();
    }

    private function createNote($patientId, $type)
    {
        if ( ! in_array($type, [Patient::BHI_REJECTION_NOTE_TYPE, Patient::BHI_CONSENT_NOTE_TYPE])) {
            throw new InvalidArgumentException("`$type` is not a valid type for a legacy BHI consent note type");
        }

        $body = $type == Patient::BHI_CONSENT_NOTE_TYPE
            ? 'The patient consented to receiving BHI services.'
            : 'The patient did not consent to receiving BHI services.';

        return Note::create([
            'patient_id' => $patientId,
            'author_id'  => 948, //clh patient support
            'body'       => $body,
            'type'       => $type,
        ]);
    }
}
