<?php

namespace App\Mail;

use App\CarePlan;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CarePlanApprovalReminder extends Mailable
{
    use Queueable, SerializesModels;
    public $numberOfCareplans;
    public $recipient;

    /**
     * Create a new message instance.
     *
     * @param User $recipient
     * @param $numberOfCareplans
     */
    public function __construct(User $recipient, $numberOfCareplans = null)
    {
        if ( ! $numberOfCareplans) {
            $numberOfCareplans = CarePlan::getNumberOfCareplansPendingApproval($recipient);
        }

        $this->recipient         = $recipient;
        $this->numberOfCareplans = $numberOfCareplans;
    }

    /**
     * Build the message.
     *
     * @return CarePlanApprovalReminder|bool
     */
    public function build()
    {
        if ($this->numberOfCareplans < 1) {
            return false;
        }

        return $this
            ->view('emails.careplansPendingApproval')
            ->from('notifications@careplanmanager.com', 'CircleLink Health')
            ->to($this->recipient->email, $this->recipient->fullName)
            ->subject("{$this->numberOfCareplans} CircleLink Care Plan(s) for your Approval!");
    }
}
