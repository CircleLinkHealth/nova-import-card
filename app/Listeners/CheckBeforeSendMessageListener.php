<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Traits\NotificationSubscribable;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Str;

class CheckBeforeSendMessageListener
{
    use NotificationSubscribable;

    /**
     * Handle the event.
     * If true will send the email.
     *
     * @param object $event
     *
     * @return bool
     */
    public function handle($event)
    {
        if ( ! empty($event->data['emailData'])) {
            $email = $event->data['emailData']['notifiableMail'];

            if (Str::contains($email, ['@careplanmanager.com'])) {
                return false;
            }

            // The User who will receive the mail.
            $user = User::whereEmail($email)->first();

            return $this->checkSubscriptions($event->data['emailData']['activityType'], $user->id);
        }

        return true;
    }
}
