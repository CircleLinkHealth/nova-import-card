<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Notifications\SAAS;

use CircleLinkHealth\Customer\Entities\Invite;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/*

Subject: [Inviter name] Invited You to [Practice or white-label customer name]’s CCM Program!

Body:

Hello!
[inviter name] has invited you to join [practice or white-label customer name]’s Chronic Care Management team at www.careplanmanager.com, in partnership with [white label customer name].

      				     [“Create Password” blue button]

Please click “create password” above to be added to the team.

Thanks!
[white label customer name] Team


 */

class SendInternalUserSignupInvitation extends Notification
{
    use Queueable;

    public $channels = ['database'];

    private $code;
    private $practice;
    private $saasAccount;
    private $sender;

    /**
     * Create a new notification instance.
     *
     * @param array|Collection|EloquentCollection|Practice $practice
     * @param array                                        $channels
     */
    public function __construct(
        User $sender,
        $practice,
        SaasAccount $saasAccount,
        $channels = ['mail']
    ) {
        if (is_array($practice) || is_a($practice, Practice::class)) {
            $practice = collect([0 => $practice]);
        }

        $this->practice = $practice;
        $this->sender   = $sender;

        $this->channels = array_merge($this->channels, $channels);

        $this->code        = generateRandomString(40);
        $this->saasAccount = $saasAccount;
    }

    public function logInvite($notifiable)
    {
        $arr = $this->toArray($notifiable);

        return Invite::create([
            'inviter_id' => $this->sender->id,
            'role_id'    => $notifiable->practiceOrGlobalRole()->id,
            'email'      => $notifiable->email,
            'subject'    => $arr['subject'],
            'message'    => $arr['greeting'].PHP_EOL.$arr['line'].PHP_EOL.$arr['action_text'].PHP_EOL,
            'code'       => $this->code,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        $inviterName  = $this->sender->getFullName();
        $practiceName = $this->practice
            ->sortBy('display_name')
            ->map(function ($practice) {
                return $practice->formatted_name;
            })
            ->implode(', ');
        $saasAccountName = $this->saasAccount->name;

        $appUrl = config('app.url');

        return [
            'subject'     => "${inviterName} Invited You to {$practiceName}’s CCM Program!",
            'greeting'    => 'Hello!',
            'line'        => "${inviterName} has invited you to join {$practiceName}’s Chronic Care Management team at ${appUrl}.",
            'action_text' => 'Create password',
            'action_link' => route('get.onboarding.create.invited.user', [
                'code' => $this->code,
            ]),
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $arr = $this->toArray($notifiable);

        $this->logInvite($notifiable);

        return (new MailMessage())
            ->subject($arr['subject'])
            ->greeting($arr['greeting'])
            ->line($arr['line'])
            ->action($arr['action_text'], $arr['action_link']);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channels;
    }
}
