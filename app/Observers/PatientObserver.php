<?php

namespace App\Observers;

use App\Patient;
use App\Services\Phaxio\PhaxioService;
use App\Services\PhiMail\PhiMail;
use Carbon\Carbon;

class PatientObserver
{
    /**
     * Listen to the Patient created event.
     *
     * @param Patient $patient
     */
    public function created(Patient $patient)
    {
        if (!$patient->consent_date) {
            return;
        }

        $this->sendPatientConsentedNote($patient);
    }

    public function sendPatientConsentedNote(Patient $patient)
    {
        if (!$patient->user->careplan->isProviderApproved()) {
            return;
        }

        $note = $patient->user->notes()->create([
            'author_id'    => 948,
            'body'         => "Patient consented on $patient->consent_date",
            'type'         => 'Patient Consented',
            'performed_at' => Carbon::now()->toDateTimeString(),
        ]);

        $pdfPath = $note->toPdf();

        if ($patient->user->primaryPractice
            ->settings
            ->first()
            ->efax_pdf_notes
        ) {
            $efax = $patient->user
                ->locations
                ->first()
                ->fax;

            if ($efax) {
                (new PhaxioService())->send($efax, $pdfPath);
            }
        }

        if ($patient->user
            ->primaryPractice
            ->settings
            ->first()
            ->dm_pdf_notes
        ) {
            $direct = $patient->user
                ->billingProvider()
                ->emr_direct_address;

            if ($direct) {
                (new PhiMail())->send($direct, $pdfPath);
            }
        }
    }

    /**
     * Listen to the Patient updated event.
     *
     * @param Patient $patient
     */
    public function updated(Patient $patient)
    {
        if (!$patient->isDirty('consent_date')) {
            return;
        }

        $this->sendPatientConsentedNote($patient);
    }
}