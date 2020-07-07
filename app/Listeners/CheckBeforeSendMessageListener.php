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

            // The User who will receive the mail.
            $user = User::whereEmail($email)->first();

            //todo return false if email contains @careplanmanager.com
            if (Str::contains($email, ['@careplanmanager.com'])) {
                return false;
            }

            return $this->checkSubscriptions($event->data['emailData']['activityType'], $user->id);
        }

        return true;
    }
}
