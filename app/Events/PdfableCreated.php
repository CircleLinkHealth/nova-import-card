<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use App\Contracts\PdfReport;
use App\Note;
use App\User;
use Illuminate\Queue\SerializesModels;

class PdfableCreated extends Event
{
    use SerializesModels;

    /**
     * An entity implementing App\Contracts\PdfReport.
     *
     * @var PdfReport
     */
    public $pdfReport;

    /**
     * Create a new event instance.
     *
     * @param User  $patient
     * @param User  $sender
     * @param Note  $note
     * @param mixed $notifyPractice
     */
    public function __construct(
        PdfReport $pdfable,
        $notifyPractice = false
    ) {
        $this->pdfReport      = $pdfable;
        $this->notifyPractice = $notifyPractice;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
