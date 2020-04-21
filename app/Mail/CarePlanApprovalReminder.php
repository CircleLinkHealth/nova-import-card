<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Mail;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CarePlanApprovalReminder extends Mailable
{
    use Queueable;
    use SerializesModels;
    public $numberOfCareplans;
    public $recipient;

    /**
     * Create a new message instance.
     *
     * @param $numberOfCareplans
     */
    public function __construct(User $recipient, $numberOfCareplans)
    {
        $this->recipient         = $recipient;
        $this->numberOfCareplans = $numberOfCareplans;
    }

    /**
     * Build the message.
     *
     * @return bool|CarePlanApprovalReminder
     */
    public function build()
    {
        return $this
            ->view('emails.careplansPendingApproval')
            ->from('notifications@careplanmanager.com', 'CircleLink Health')
            ->to($this->recipient->email, $this->recipient->getFullName())
            ->subject("{$this->numberOfCareplans} CircleLink Care Plan(s) for your Approval!");
    }
}
