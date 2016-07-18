<?php

namespace App\Services;

use App\Activity;
use App\Call;
use App\MailLog;
use App\Note;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Mockery\Matcher\Not;

class NoteService
{


    public function storeNote($input)
    {
        $note = Note::create($input);

        if ($input['tcm'] == 'true') {
            $note->isTCM = true;
        } else {
            $note->isTCM = false;
        } $note->save();

        $patient = User::find($note->patient_id);
        $author = User::find($input['author_id']);

        if(isset($input['phone'])) {
            $this->storeCallForNote($note, $input['call_status'], $patient, $author, $input['phone']);
        }

        // update usermeta: cur_month_activity_time
        $activityService = new ActivityService;
        $activityService->reprocessMonthlyActivityTime($input['patient_id']);
        $linkToNote = route('patient.note.view', array('patientId' => $note->patient_id, 'noteId' => '')) . '/' . $note->id;
        $logger = User::find($input['logger_id']);
        $logger_name = $logger->display_name;

        //if emails are to be sent

        if (array_key_exists('careteam', $input)) {

            $this->sendNoteToCareTeam($note, $input['careteam'], $linkToNote, true);

        } else if ($note->isTCM) {

            $user_care_team = $patient->sendAlertTo;

            $result = $this->sendNoteToCareTeam($note, $user_care_team, $linkToNote, true);
        }
    }

    //NOTE RETRIEVALS (ranges, relations, owners)

    //Get all notes for patient
    public function getNotesForPatient(User $patient)
    {
        return Note::where('patient_id',$patient->ID)->get();
    }

    //Get note with mail
    public function getNoteWithCommunications($note_id)
    {

        return Note::where('id',$note_id)->with('call')->with('mail')->first();

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
    public function getNotesWithRangeForPatients($patients, $start, $end)
    {

        return Note::whereIn('patient_id', $patients)
            ->whereBetween('performed_at', [
                $start, $end
            ])
            ->orderBy('performed_at', 'desc')
            ->with('patient')->with('mail')->with('call')->with('author')
            ->get();

    }

    //Get all notes that were forwarded with specified date range
    public function getForwardedNotesWithRangeForPatients($patients, $start, $end)
    {

        return Note::whereIn('patient_id', $patients)
            ->whereBetween('performed_at', [
                $start, $end
            ])
            ->has('mail')
            ->orderBy('performed_at', 'desc')
            ->with('patient')->with('mail')->with('call')->with('author')
            ->get();

    }

    //Get all notes for a given provider with specified date range
    public function getNotesWithRangeForProvider($provider, $start, $end)
    {

        $patients = User::whereHas('patientCareTeamMembers',

            function($q) use ($provider) {

                $q->where('member_user_id', $provider)
                    ->where('type','billing_provider');
            })->lists('ID');

        return $this->getNotesWithRangeForPatients($patients, $start, $end);

    }

    //Get all notes that have been sent to anyone for a given provider with specified date range
    public function getForwardedNotesWithRangeForProvider($provider, $start, $end)
    {

        $patients = User::whereHas('patientCareTeamMembers',

            function($q) use ($provider) {

                $q->where('member_user_id', $provider)
                    ->where('type','billing_provider');
            })->lists('ID');

        $notes = $this->getForwardedNotesWithRangeForPatients($patients, $start, $end);

        $provider_forwarded_notes = array();
//        dd($notes);

        foreach ($notes as $note){

            if($this->wasSentToProvider($note)){
                $provider_forwarded_notes[] = $note;
            }
        }

        return collect($provider_forwarded_notes);

    }

    //Save call information for note
    public function storeCallForNote($note, $status, User $patient, User $author, $phone_direction)
    {

        $patient = User::find($note->patient_id);
        $author = User::find($note->logger_id);


        if ($phone_direction == 'inbound') {
            $outbound_num = $patient->primaryPhone;
            $outbound_id = $patient->ID;
            $inbound_num = $author->primaryPhone;
            $inbound_id = $author->ID;
            $isCpmOutbound = false;
        } else {
            $outbound_num = $author->primaryPhone;
            $outbound_id = $author->ID;
            $inbound_num = $patient->primaryPhone;
            $inbound_id = $patient->ID;
            $isCpmOutbound = true;

        }

        Call::create([

            'note_id' => $note->id,
            'service' => 'phone',
            'status' => $status,

            'inbound_phone_number' => $outbound_num,
            'outbound_phone_number' => $inbound_num,

            'inbound_cpm_id' => $inbound_id,
            'outbound_cpm_id' => $outbound_id,

            //@todo figure out call times!

            'call_time' => 0,
            'created_at' => $note->performed_at,

            'is_cpm_outbound' => $isCpmOutbound

        ]);
//            }
//        }
    }

    //MAIL HELPERS

    //send notes when stored
    public function sendNoteToCareTeam(Note $note, &$careteam, $url, $newNoteFlag)
    {

        /*
         *  New note: "Please see new note for patient [patient name]: [link]"
         *  Old/Fw'd note: "Please see forwarded note for patient [patient  name], created on [creation date] by [note creator]: [link]
         */

        $patient = User::find($note->patient_id);
        $sender = User::find($note->logger_id);

        for ($i = 0; $i < count($careteam); $i++) {

            $receiver = User::find($careteam[$i]);

            if(is_object($receiver) == false){
                continue;
            }

            $email = $receiver->user_email;

            $performed_at = Carbon::parse($note->performed_at)->toFormattedDateString();

            $data = array(
                'patient_name' => $patient->fullName,
                'url' => $url,
                'time' => $performed_at,
                'logger' => $sender->fullName
            );

            if ($newNoteFlag || $note->isTCM) {
                $email_view = 'emails.newnote';
                $email_subject = 'Urgent Patient Note from CircleLink Health';
            } else {
                $email_view = 'emails.existingnote';
                $email_subject = 'You have received a new note notification from CarePlan Manager';
            }

            Mail::send($email_view, $data, function ($message) use ($email, $email_subject) {
                $message->from('no-reply@careplanmanager.com', 'CircleLink Health');

                //Forwards notes to Linda
                $message->cc('Lindaw@circlelinkhealth.com');
                $message->to($email)->subject($email_subject);
            });

            if($newNoteFlag){
                $body = 'Please see new note for patient ' . $patient->fullName . ':' . $url;
            } else {
                $body = 'Please see forwarded note for patient ' . $patient->fullName . ', created on ' . $performed_at  . ' by '.$sender->fullName.':' . $url;
            }

            MailLog::create([
                'sender_email' => $sender->user_email,
                'receiver_email' => $receiver->user_email,
                'body' => $body,
                'subject' => $email_subject,
                'type' => 'note',
                'sender_cpm_id' => $sender->ID,
                'receiver_cpm_id' => $receiver->ID,
                'created_at' => $note->created_at,
                'note_id' => $note->id
            ]);

        }
        return true;

    }

    //note sender
    public function forwardNote($input, $patientId){

        if (isset($input['careteam'])) {

            $note = Note::findOrFail($input['noteId']);

            $author = User::find($input['logger_id']);

            if(is_object($author)){
                $author_name = $author->fullName;
            } else {
                $author_name = '';
            }

            $linkToNote = URL::route('patient.note.view', array('patientId' => $patientId, 'noteId' => $note->id));

            $result = $this->sendNoteToCareTeam(
                $note,
                $input['careteam'],
                $linkToNote,
                false);
        }

        return true;
    }

    //return bool of whether note was sent to a provider
    public function wasSentToProvider(Note $note){

        $mails = $note->mail;

        if(count($mails) < 1){
            return false;
        }

        foreach ($mails as $mail){
            $mail_recipient = User::find($mail->receiver_cpm_id);
            if($mail_recipient->hasRole('provider')){
                debug($mail->receiver_cpm_id);
                return true;
            }
        }

        return false;
    }

}