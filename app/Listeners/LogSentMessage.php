<?php

namespace App\Listeners;

use App\MailLog;
use App\User;
use Illuminate\Mail\Events\MessageSending;
use Maknz\Slack\Facades\Slack;

class LogSentMessage
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MessageSending $event
     *
     * @return void
     */
    public function handle(MessageSending $event)
    {
        try {
            $sender_email = array_keys($event->message->getFrom())[0];
            $receiver_email = array_keys($event->message->getTo())[0];
            $body = $event->message->getBody();
            $subject = $event->message->getSubject();
            $type = 'email';

            $sender = User::whereEmail($sender_email)->first();
            $receiver = User::whereEmail($receiver_email)->first();

            if ($receiver->primaryPractice->auto_approve_careplans) {
                return false;
            }

            if (!$receiver->primaryPractice->send_alerts) {
                return false;
            }

            $sender_cpm_id = $sender->id ?? 357;
            $receiver_cpm_id = $receiver->id ?? 357;

            MailLog::create([
                'sender_email'    => $sender_email,
                'receiver_email'  => $receiver_email,
                'body'            => $body,
                'subject'         => $subject,
                'type'            => $type,
                'sender_cpm_id'   => $sender_cpm_id,
                'receiver_cpm_id' => $receiver_cpm_id,
            ]);
        } catch (\Exception $e) {
            $environment = env('APP_ENV');
            $exceptionLocation = __METHOD__ . ' ' . __LINE__;
            $message = "Failed to log sent email. Message: {$e->getMessage()}. At Method/Line: $exceptionLocation. Env: $environment";

            \Log::alert($message);
            Slack::to('#dev-chat')->send($message);
        }

    }
}
