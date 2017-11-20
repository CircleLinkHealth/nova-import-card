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
    protected $numberOfCareplans;
    protected $recipient;

    /**
     * Create a new message instance.
     *
     * @param User $recipient
     * @param $numberOfCareplans
     */
    public function __construct(User $recipient, $numberOfCareplans = null)
    {
        if (!$numberOfCareplans) {
            $numberOfCareplans = CarePlan::getNumberOfCareplansPendingApproval($recipient);
        }

        if ($numberOfCareplans < 1) {
            return false;
        }

        $this->recipient = $recipient;
        $this->numberOfCareplans = $numberOfCareplans;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'numberOfCareplans' => $this->numberOfCareplans,
            'recipient'         => $this->recipient,
        ];

        $view = 'emails.careplansPendingApproval';
        $subject = "{$this->numberOfCareplans} CircleLink Care Plan(s) for your Approval!";

        return $this->from('notifications@careplanmanager.com', 'CircleLink Health')
            ->to($this->recipient->email, $this->recipient->fullName)
            ->subject($subject)
            ->view($view)
            ->with($data);
    }
}
