<?php

namespace App\Services;

use App\Activity;
use App\Call;
use App\Note;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        } $note->save(); //update note

        $patient = User::find($note->patient_id);
        $author = User::find($note->author_id);

        $this->storeCallForNote($note, $input['call_status'], $patient, $author, $input['phone']);

        // update usermeta: cur_month_activity_time
        $activityService = new ActivityService;
        $activityService->reprocessMonthlyActivityTime($input['patient_id']);
        $linkToNote = URL::route('patient.note.view', array('patientId' => $note->patient_id)) . '/' . $note->id;
        $logger = User::find($input['logger_id']);
        $logger_name = $logger->display_name;

        //if emails are to be sent

        if (array_key_exists('careteam', $input)) {
            //Log to Meta Table
            foreach ($input['careteam'] as $item){
                    // @todo add support for body and subject
                $this->saveMailLogForNote($note, $logger, $patient, '', '');
            }

            (new ActivityService())->sendNoteToCareTeam($input['careteam'], $linkToNote, $input['performed_at'], $input['patient_id'], $logger_name, true, $note->isTCM);

        } else if ($note->isTCM) {

            $user_care_team = $patient->sendAlertTo;

            $result = (new ActivityService())->sendNoteToCareTeam($user_care_team, $linkToNote, $input['performed_at'], $input['patient_id'], $logger_name, true, $note->isTCM);
        }
    }

    public function getNotesForPatient(User $patient)
    {

        return DB::table('lv_activities')
            ->select(DB::raw('*,provider_id, type'))
            ->where('patient_id', $patient->ID)
            ->where(function ($q) {
                $q->where('logged_from', 'note')
                    ->Orwhere('logged_from', 'manual_input');
            })
            ->orderBy('performed_at', 'desc')
            ->get();
    }

    public function getNotesAndOfflineActivitiesForPatient(User $patient)
    {

        // @todo figure out compiling these sections together
        $notes = $this->getNotesForPatient($patient);
        $activities = $acts = DB::table('lv_activities')
            ->select(DB::raw('*'))
            ->where('patient_id', $patient->ID)
            ->where('logged_from', 'manual_input')
            ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
            ->orderBy('performed_at', 'desc')
            ->get();

        //Convert to Collections
        $activities = collect($activities);
        $notes = collect($notes);

        $data = $notes->merge($activities)->sortByDesc('performed_at');

        return $data;

    }

    public function getNotesWithRangeForPatients($patients, $start, $end)
    {

        return Note::whereIn('patient_id', $patients)
            ->whereBetween('created_at', [
                $start, $end
            ])
            ->orderBy('created_at', 'desc')->get();

    }


    public function getMonthsArray()
    {
        return array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    }

    public function getYearsArray()
    {

        $years = array();
        for ($i = 0; $i < 3; $i++) {
            $years[] = Carbon::now()->subYear($i)->year;
        }
        array_reverse($years);

        return $years;

    }

    public function storeCallForNote($note, $status, User $patient, User $author, $phone_direction)
    {

        $patient = User::find($note->patient_id);
        $author = User::find($note->logger_id);


                if ($phone_direction == 'inbound') {
                    $outbound_num = $patient->primaryPhone;
                    $outbound_id = $patient->ID;
                    $inbound_num = $author->primaryPhone;
                    $inbound_id = $author->ID;
                } else {
                    $outbound_num = $author->primaryPhone;
                    $outbound_id = $author->ID;
                    $inbound_num = $patient->primaryPhone;
                    $inbound_id = $patient->ID;
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
                    'created_at' => $note->performed_at

                ]);
//            }
//        }
    }

    public function saveMailLogForNote($note, User $sender, User $receiver, $body, $subject){

        \App\MailLog::create([
            'sender_email' => $sender->user_email,
            'receiver_email' => $receiver->user_email,
            'body' => '',
            'subject' => '',
            'type' => 'note',
            'sender_cpm_id' => $sender->ID,
            'receiver_cpm_id' => $receiver->ID,
            'created_at' => $note->created_at,
            'note_id' => $note->id
        ]);

    }

}