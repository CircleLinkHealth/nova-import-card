<?php

use App\DatabaseNotification;
use App\MailLog;
use App\Note;
use App\Notifications\NoteForwarded;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Notifications\Messages\SimpleMessage;

class MigrateCpmMail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        MailLog::chunk(5000, function ($logs) {
            foreach ($logs as $log) {
                DatabaseNotification::create([
                    'id'              => "mailLog_$log->id",
                    'type'            => $log->note_id
                        ? NoteForwarded::class
                        : SimpleMessage::class,
                    'notifiable_id'   => $log->receiver_cpm_id,
                    'notifiable_type' => User::class,
                    'attachment_id'   => $log->note_id ?? null,
                    'attachment_type' => $log->note_id
                        ? Note::class
                        : null,
                    'data'            => $log->toJson(),
                    'read_at'         => $log->seen_on ?? $log->created_at,
                    'created_at'      => $log->created_at,
                    'updated_at'      => $log->created_at,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
