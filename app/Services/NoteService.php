<?php

namespace App\Services;

use App\Call;
use App\Events\NoteWasForwarded;
use App\MailLog;
use App\Note;
use App\Notifications\NewNote;
use App\PatientInfo;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
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
        $note->save();

        $patient = User::find($note->patient_id);

        // update usermeta: cur_month_activity_time
        $activityService = new ActivityService;
        $activityService->reprocessMonthlyActivityTime($input['patient_id']);
        $linkToNote = URL::route('patient.note.view', [
            'patientId' => $note->patient_id,
            'noteId'    => $note->id,
        ]);// . '/' . $note->id;
        $logger = User::find($input['logger_id']);
        $logger_name = $logger->display_name;

        //if emails are to be sent

        if (array_key_exists('careteam', $input)) {

            $this->sendNoteToCareTeam($note, $input['careteam'], $linkToNote, true);

        } else {
            if ($note->isTCM) {

                $user_care_team = $patient->sendAlertTo;

                $this->sendNoteToCareTeam($note, $user_care_team, $linkToNote, true);
            }
        }

        return $note;
    }

    //NOTE RETRIEVALS (ranges, relations, owners)

    //Get all notes for patient

    public function sendNoteToCareTeam(
        Note $note,
        &$careteam,
        $url,
        $newNoteFlag
    ) {

        /*
         *  New note: "Please see new note for patient [patient name]: [link]"
         *  Old/Fw'd note: "Please see forwarded note for patient [patient  name], created on [creation date] by [note creator]: [link]
         */

        $patient = User::find($note->patient_id);
        $sender = User::find($note->logger_id);

        event(new NoteWasForwarded($patient, $sender, $note, $careteam));

        for ($i = 0; $i < count($careteam); $i++) {

            $receiver = User::find($careteam[$i]);

            if (is_object($receiver) == false) {
                continue;
            }

            $email = $receiver->email;

            $performed_at = Carbon::parse($note->performed_at)->toFormattedDateString();

            $data = [
                'patient_name' => $patient->fullName,
                'url'          => $url,
                'time'         => $performed_at,
                'logger'       => $sender->fullName,
            ];

            if ($newNoteFlag || $note->isTCM) {
                $email_subject = 'Urgent Patient Note from CircleLink Health';
            } else {
                $email_subject = 'You have been forwarded a note from CarePlanManager';
            }

            if ($newNoteFlag) {
                $body = 'Please see note for one of your patients';
            } else {
                $body = 'Please see forwarded note for one of your patients, created on ' . $performed_at . ' by ' . $sender->fullName;
            }

            $message = MailLog::create([
                'sender_email'    => $sender->email,
                'receiver_email'  => $receiver->email,
                'body'            => $body,
                'subject'         => $email_subject,
                'type'            => 'note',
                'sender_cpm_id'   => $sender->id,
                'receiver_cpm_id' => $receiver->id,
                'created_at'      => $note->created_at,
                'note_id'         => $note->id,
            ]);

            $receiver->notify(new NewNote($message, $url));

        }

        return true;

    }

    //Get note with mail

    public function getNoteWithCommunications($note_id)
    {

        return Note::where('id', $note_id)->with('call')->with('mail')->first();

    }

    //Get data for patient note index page, w/ offline activities
    public function getNotesAndOfflineActivitiesForPatient(User $patient)
    {

        // @todo figure out compiling these sections together
        $notes = $this->getNotesForPatient($patient);

        $activities = (new ActivityService())->getOfflineActivitiesForPatient($patient);

        //Convert to Collections
        $activities = collect($activities);
        $notes = collect($notes);

        $data = $notes->merge($activities)->sortByDesc('performed_at');

        return $data;

    }

    //Get all notes for patients with specified date range

    public function getNotesForPatient(User $patient)
    {
        return Note::where('patient_id', $patient->id)->get();
    }

    //Get all notes that were forwarded with specified date range

    public function getNotesWithRangeForProvider(
        $provider,
        $start,
        $end
    ) {

        $patients = User::whereHas('patientCareTeamMembers',

            function ($q) use
            (
                $provider
            ) {

                $q->where('member_user_id', $provider)
                    ->where('type', 'billing_provider');
            })->pluck('id');

        return $this->getNotesWithRangeForPatients($patients, $start, $end);

    }

    //Get all notes for a given provider with specified date range

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
            ->with('patient')->with('mail')->with('call')->with('author')
            ->get();

    }

    //Get all notes that have been sent to anyone for a given provider with specified date range

    public function getForwardedNotesWithRangeForProvider($provider, $start, $end) {

            $patients = User::whereHas('patientCareTeamMembers', function ($q) use
            (
                $provider
            ) {
                $q->where('member_user_id', $provider)
                    ->where('type', 'billing_provider');
            })->pluck('id');
        
            $notes = $this->getForwardedNotesWithRangeForPatients($patients, $start, $end);

            $provider_forwarded_notes = [];

            foreach ($notes as $note) {

                if ($this->wasSentToProvider($note)) {
                    $provider_forwarded_notes[] = $note;
                }
            }

            return collect($provider_forwarded_notes);

    }

    //Save call information for note
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
            ->has('mail')
            ->orderBy('performed_at', 'desc')
            ->with('patient')->with('mail')->with('call')->with('author')
            ->get();

    }

    public function wasSentToProvider(Note $note)
    {

        $mails = $note->mail;

        if (count($mails) < 1) {
            return false;
        }

        foreach ($mails as $mail) {

            $mail_recipient = User::find($mail->receiver_cpm_id);

            if ($mail_recipient->hasRole('provider')) {

                return true;
            }
        }

        return false;
    }

    public function wasReadByBillingProvider(Note $note)
    {

        $mails = $note->mail;

        if (count($mails) < 1) {
            return false;
        }

        foreach ($mails as $mail) {

            $mail_recipient = User::find($mail->receiver_cpm_id);
            $patient = User::find($note->patient_id);

            if ($mail_recipient->id == $patient->billingProvider()->id && $mail->seen_on != null) {

                return true;

            }
        }

        return false;
    }

    //MAIL HELPERS

    //send notes when stored

    public function getAllForwardedNotesWithRange(Carbon $start, Carbon $end){

        $patients = User::ofType('participant')
            ->get()
            ->pluck('id');

        $notes = Note::whereIn('patient_id', $patients)
            ->whereBetween('created_at', [
                $start,
                $end,
            ])
            ->has('mail')
            ->orderBy('performed_at', 'desc')
            ->with('patient')->with('mail')->with('call')->with('author')
            ->get();

        $provider_forwarded_notes = [];

        foreach ($notes as $note) {

            if ($this->wasSentToProvider($note)) {
                $provider_forwarded_notes[] = $note;
            }
        }

        return collect($provider_forwarded_notes);

    }

    //note sender

    public function updateMailLogsForNote($viewer, Note $note){

        $mail = MailLog::where('note_id', $note->id)
            ->where('receiver_cpm_id', $viewer)->first();

        if(is_object($mail)){

            $mail->seen_on = Carbon::now()->toDateTimeString();
            $mail->save();

        }

    }

    public function getSeenForwards(Note $note){

        $mails = MailLog::where('note_id', $note->id)
            ->whereNotNull('seen_on')->get();

        $data = [];

        foreach ($mails as $mail){

            $name = User::find($mail->receiver_cpm_id)->fullName;
            $data[$name] = $mail->seen_on;

        }

        if(count($data) > 0){
            return $data;
        }

        return false;

    }

    public function forwardedNoteWasSeenByPrimaryProvider(Note $note, PatientInfo $patient){

        $mail = MailLog::where('note_id', $note->id)
            ->where('receiver_cpm_id', $patient->billingProvider()->id)->first();

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
            $outbound_num = $patient->primaryPhone;
            $outbound_id = $patient->id;
            $inbound_num = $author->primaryPhone;
            $inbound_id = $author->id;
            $isCpmOutbound = false;
        } else {
            $outbound_num = $author->primaryPhone;
            $outbound_id = $author->id;
            $inbound_num = $patient->primaryPhone;
            $inbound_id = $patient->id;
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

    public function forwardNote(
        $input,
        $patientId
    ) {

        if (isset($input['careteam'])) {

            $note = Note::findOrFail($input['noteId']);

            $author = User::find($input['logger_id']);

            if (is_object($author)) {
                $author_name = $author->fullName;
            } else {
                $author_name = '';
            }

            $linkToNote = route('patient.note.view', [
                'patientId' => $patientId,
                'noteId'    => $note->id,
            ]);

            $result = $this->sendNoteToCareTeam(
                $note,
                $input['careteam'],
                $linkToNote,
                false);
        }

        return true;
    }

}