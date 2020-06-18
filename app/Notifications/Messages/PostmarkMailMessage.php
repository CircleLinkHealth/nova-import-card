<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Messages;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Notifications\Messages\MailMessage;

class PostmarkMailMessage extends MailMessage
{
    private $notifiable;
    /**
     * The practice name.
     * If the $notifiable is a participant, we want to show the practice name instead of CLH.
     *
     * @var string|null
     */
    private $practiceName;

    public function __construct($notifiable)
    {
        $this->notifiable = $notifiable;

        if (config('mail.transactional_from.address')) {
            $this->usePostmarkMail();
        } else {
            $this->useMail();
        }
    }

    private function getPracticeName()
    {
        if (is_null($this->practiceName)) {
            if ($this->notifiable instanceof User && $this->notifiable->isParticipant()) {
                $this->practiceName = $this->notifiable->getPrimaryPracticeName();
            } else {
                //This will make sure we only attempt to get practice name once.
                //Otherwise if the notifiable is not a User, or is a User but not a participant it will try to get the practice name whenever this method is called.
                $this->practiceName = false;
            }
        }

        return $this->practiceName;
    }

    private function useMail()
    {
        $this->from(config('mail.from.address'), $this->getPracticeName() ?? config('mail.from.name'));
    }

    private function usePostmarkMail()
    {
        $this->mailer('postmark')
            ->from(config('mail.transactional_from.address'), $this->getPracticeName() ?? config('mail.transactional_from.name'));
    }
}
