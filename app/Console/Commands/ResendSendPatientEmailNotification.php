<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Notifications\SendPatientEmail;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use Illuminate\Console\Command;

class ResendSendPatientEmailNotification extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend a notification of type App\Notifications\SendPatientEmail';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend-notification:send-patient-email {uuid}';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $uuid = $this->argument('uuid');

        if ( ! empty($uuid)) {
            $this->warn("Resending Notification[$uuid]");
            self::resend($uuid);
        }

        $this->line('Job ran.');
    }

    public static function resend(string $uuid)
    {
        with(DatabaseNotification::whereId($uuid)
            ->with('notifiable')
            ->where('type', SendPatientEmail::class)
            ->firstOrFail(), function ($dn) {
                $dn->notifiable->notify(new SendPatientEmail(
                    $dn->notifiable,
                    $dn->data['sender_id'],
                    $dn->data['email_content'],
                    $dn->data['attachments'],
                    $dn->data['note_id'],
                    $dn->data['email_subject']
                ));
            });
    }
}
