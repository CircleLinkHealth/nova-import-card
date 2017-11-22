<?php

namespace App\Services;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Call;
use App\CarePerson;
use App\Note;
use App\Patient;
use App\PatientMonthlySummary;
use App\User;
use App\View\MetaTag;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class NoteService
{

    public function storeNote($input)
    {
        $note = Note::create($input);

        if ($input['tcm'] == 'true') {
            $note->isTCM = true;
        } else {
            $note->isTCM = false;
        }

        if (isset($input['medication_recon'])) {
            $note->did_medication_recon = true;
        } else {
            $note->did_medication_recon = false;
        }

        $note->save();

        $note->forward($input['notify_careteam'] ?? false, $input['notify_circlelink_support'] ?? false);

        return $note;
    }

    //Get all notes for patients with specified date range

//    public function getNotesAndOfflineActivitiesForPatient(User $patient)
//    {
//        $notes = $patient->notes;
//        $activities = $patient->activities;
//        $appointments = $patient->appointments;
//
//        return $notes->merge($activities)
//            ->merge($appointments);
//    }

    //Get all notes that have been sent to anyone for a given provider with specified date range

    public function getNotesWithRangeForProvider(
        $provider,
        $start,
        $end
    ) {

        $patients = User::whereHas(
            'careTeamMembers',
            function ($q) use (
                $provider
            ) {

                $q->where('member_user_id', $provider)
                  ->where('type', 'billing_provider');
            }
        )->pluck('id');

        return $this->getNotesWithRangeForPatients($patients, $start, $end);
    }

    //Save call information for note

    public function getNotesWithRangeForPatients(
        $patients,
        $start,
        $end
    ) {

        return Note::whereIn('patient_id', $patients)
                   ->whereBetween('performed_at', [
                       $start,
                       $end,
                   ])
                   ->orderBy('performed_at', 'desc')
                   ->with('patient')->with(['call', 'notifications', 'author'])
                   ->get();
    }

    public function getForwardedNotesWithRangeForProvider(
        $provider,
        $start,
        $end
    ) {

        $patients = User::whereHas('careTeamMembers', function ($q) use (
            $provider
        ) {
            $q->where('member_user_id', $provider)
              ->where('type', 'billing_provider');
        })->pluck('id');

        $notes = $this->getForwardedNotesWithRangeForPatients($patients, $start, $end);

        $provider_forwarded_notes = [];

        foreach ($notes as $note) {
            if ($note->wasForwardedToCareTeam()) {
                $provider_forwarded_notes[] = $note;
            }
        }

        return collect($provider_forwarded_notes);
    }

    public function getForwardedNotesWithRangeForPatients(
        $patients,
        $start,
        $end
    ) {

        return Note::whereIn('patient_id', $patients)
                   ->whereBetween('performed_at', [
                       $start,
                       $end,
                   ])
                   ->has('notifications')
                   ->orderBy('performed_at', 'desc')
                   ->with(['patient', 'call', 'notifications', 'author'])
                   ->get();
    }

    public function getAllForwardedNotesWithRange(
        Carbon $start,
        Carbon $end
    ) {

        $patients = User::ofType('participant')
                        ->get()
                        ->pluck('id');

        $notes = Note::whereIn('patient_id', $patients)
                     ->whereBetween('created_at', [
                         $start,
                         $end,
                     ])
                     ->has('notifications')
                     ->orderBy('performed_at', 'desc')
                     ->with(['patient', 'call', 'notifications', 'author'])
                     ->get();

        $provider_forwarded_notes = [];

        foreach ($notes as $note) {
            if ($note->wasForwardedToCareTeam()) {
                $provider_forwarded_notes[] = $note;
            }
        }

        return collect($provider_forwarded_notes);
    }

    public function markNoteAsRead(
        User $viewer,
        Note $note
    ) {
        $viewer->unreadNotifications()
               ->hasNotifiableType(User::class)
               ->hasAttachmentType(Note::class)
               ->where('attachment_id', '=', $note->id)
               ->get()
               ->markAsRead();
    }

    public function updatePatientRecords(
        Patient $patient,
        $ccmComplex
    ) {

        $date_index = Carbon::now()->firstOfMonth()->toDateString();

        $patientRecord = $patient
            ->monthlySummaries
            ->where('month_year', $date_index)->first();

        if (empty($patientRecord)) {
            $patientRecord = PatientMonthlySummary::updateCCMInfoForPatient(
                $patient,
                $patient->cur_month_activity_time
            );
        } else {
            $patientRecord->is_ccm_complex = 0;
            $patientRecord->save();
        }

        if ($ccmComplex) {
            $patientRecord->is_ccm_complex = 1;
            $patientRecord->save();

            if ($patient->cur_month_activity_time > 3600 && auth()->user()->nurseInfo) {
                (new AlternativeCareTimePayableCalculator(auth()->user()->nurseInfo))->adjustPayOnCCMComplexSwitch60Mins();
            }
        }

        return $patientRecord;
    }

    public function storeCallForNote(
        $note,
        $status,
        User $patient,
        User $author,
        $phone_direction,
        $scheduler,
        $attemptNote = ''
    ) {

        if ($phone_direction == 'inbound') {
            $outbound_num  = $patient->primaryPhone;
            $outbound_id   = $patient->id;
            $inbound_num   = $author->primaryPhone;
            $inbound_id    = $author->id;
            $isCpmOutbound = false;
        } else {
            $outbound_num  = $author->primaryPhone;
            $outbound_id   = $author->id;
            $inbound_num   = $patient->primaryPhone;
            $inbound_id    = $patient->id;
            $isCpmOutbound = true;
        }

        Call::create([

            'note_id' => $note->id,
            'service' => 'phone',
            'status'  => $status,

            'scheduler' => $scheduler,

            'attempt_note' => $attemptNote,

            'inbound_phone_number'  => $outbound_num,
            'outbound_phone_number' => $inbound_num,

            'inbound_cpm_id'  => $inbound_id,
            'outbound_cpm_id' => $outbound_id,

            //@todo figure out call times!
            'called_date'     => Carbon::now()->toDateTimeString(),

            'call_time'  => 0,
            'created_at' => $note->performed_at,

            'is_cpm_outbound' => $isCpmOutbound,

        ]);
    }

    //return bool of whether note was sent to a provider

    public function getPatientCareTeamMembers($patientId)
    {

        $careteam_info = [];
        $careteam_ids  = CarePerson
            ::whereUserId($patientId)->pluck('member_user_id');

        foreach ($careteam_ids as $id) {
            if (User::find($id)) {
                $careteam_info[$id] = User::find($id)->fullName;
            }
        }

        return $careteam_info;
    }

    public function tags(Note $note)
    {
        $meta_tags = [];

        if ($note->call) {
            if ($note->call->is_cpm_inbound) {
                $meta_tags[] = new MetaTag('info', 'Inbound Call');
            } else {
                $meta_tags[] = new MetaTag('info', 'Outbound Call');
            }

            if ($note->call->status == 'reached') {
                $meta_tags[] = new MetaTag('info', 'Successful Clinical Call');
            }
        }

        $readers = $this->getSeenForwards($note);

        if ($readers->count() > 0) {
            $meta_tags[] = new MetaTag('info', 'Forwarded', $readers->keys()->implode(', '));
        }

        if ($note->isTCM) {
            $meta_tags[] = new MetaTag('danger', 'Patient Recently in Hospital/ER');
        }

        if ($note->did_medication_recon) {
            $meta_tags[] = new MetaTag('info', 'Medication Reconciliation');
        }

        return $meta_tags;
    }

    public function getSeenForwards(Note $note)
    {
        return $note->notifications()
                    ->hasNotifiableType(User::class)
                    ->with('notifiable')
                    ->whereNotNull('read_at')
                    ->get()
                    ->mapWithKeys(function ($notification) {
                        return [$notification->notifiable->fullName => $notification->read_at->format('m/d/y h:iA T')];
                    });
    }
}
