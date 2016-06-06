<?php

use App\ActivityMeta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class NotesTableSeeder extends Seeder
{

    public function run()
    {

        $this->command->info('Initializing Notes Migration...');
        $this->command->line('');
        $activity_notes = DB::table('lv_activities')
            ->select(DB::raw('*,provider_id, type'))
            ->where('logged_from', 'note')
//            ->where('id',55360) tester
            ->orderBy('performed_at', 'desc')
            ->get();

        $this->command->info('Found ' . count($activity_notes) .  ' ...');
        $this->command->line('');

        foreach ($activity_notes as $activity_note) {

            $this->command->warn("Transferring Activity ID: " . $activity_note->id);

            //Retrieve Comment
            $metaComment = ActivityMeta::where('activity_id', $activity_note->id)
                ->where('meta_key', 'comment')->first();

            if (is_object($metaComment)) {
                $comment = $metaComment->meta_value;
            } else {
                $comment = '';
            }

            //Check if ER box was checked
            $tcm = ActivityMeta::where('activity_id', $activity_note->id)
                ->where('meta_key', 'hospital')->first();

            $tcm_flag = false;

            if (is_object($tcm)){
                    $tcm_flag = true;
            }

            $call_direction = ActivityMeta::where('activity_id', $activity_note->id)
                ->where('meta_key', 'phone')
                ->get()->first();

            $call_status = ActivityMeta::where('activity_id', $activity_note->id)
                ->where('meta_key', 'call_status')
                ->get()->first();

            $sender_meta = ActivityMeta::where('activity_id', $activity_note->id)
                ->where('meta_key', 'email_sent_by')->get();

            $receiver_meta = ActivityMeta::where('activity_id', $activity_note->id)
                ->where('meta_key', 'email_sent_to')->get();

            if (is_object($sender_meta)) {

                foreach ($sender_meta as $meta_s) {
                    foreach ($receiver_meta as $meta_r) {

                        //Only way to check if these entries are connected is by time, db tested.

                        if ($meta_s->created_at == $meta_r->created_at) {

                            //parsing " id1, id2, id3" for multiple receivers.

                            $sender = \App\User::find($meta_s->meta_value);
                            $sender_email = $sender->user_email;

                            $receivers = $meta_r->meta_value;
                            $receivers = explode(', ', $receivers);

                            foreach ($receivers as $receiver) {

                                $receiver = \App\User::find(trim($receiver));

                                if (is_object($receiver)) {

                                    $receiver_email = $receiver->user_email;

                                    $cpm_mail_log = \App\MailLog::create([
                                        'sender_email' => $sender_email,
                                        'receiver_email' => $receiver_email,
                                        'body' => '',
                                        'subject' => '',
                                        'type' => 'note',
                                        'sender_cpm_id' => $sender->ID,
                                        'receiver_cpm_id' => $receiver->ID,
                                        'created_at' => $activity_note->created_at
                                    ]);
                                }
                                $this->command->info("Mail Logged - cpm_mail_log id: " . $cpm_mail_log->id);
                            }
                        }
                    }
                }
            }

            $note = \App\Note::create([
                'patient_id' => $activity_note->patient_id,
                'author_id' => $activity_note->logger_id,
                'body' => $comment,
                'isTCM' => $tcm_flag,
                'created_at' => $activity_note->created_at,
                'type' => $activity_note->type
            ]);

            $this->command->info("Transferred Note ID: " . $note->id);

            $patient = \App\User::find($activity_note->patient_id);
            $author = \App\User::find($activity_note->logger_id);

            if(is_object($patient) && is_object($author)) {
                if (is_object($call_direction)) {

                    if (is_object($call_status)) {
                        $status = 'reached';
                    } else {
                        $status = 'not reached';
                    }

                    if ($call_direction == 'inbound') {
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

                    $call = \App\Call::create([

                        'note_id' => $note->id,
                        'service' => 'phone',
                        'status' => $status,

                        'inbound_phone_number' => $outbound_num,
                        'outbound_phone_number' => $inbound_num,

                        'inbound_cpm_id' => $inbound_id,
                        'outbound_cpm_id' => $outbound_id,

                        //?
                        'call_time' => '',
                        'created_at' => $activity_note->created_at


                    ]);
                    $this->command->info("Call created for Note: " . $call->id);
                }
            }
            $this->command->info("Successfully Migrated to Note id: " . $note->id);
        }
        $this->command->line('');
    }
}