<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use App\Contracts\PdfReport;
use Illuminate\Queue\SerializesModels;

class PdfableCreated extends Event
{
    use SerializesModels;
    /**
     * @var bool|mixed
     */
    public $notifyPractice;

    /**
     * An entity implementing App\Contracts\PdfReport.
     *
     * @var PdfReport
     */
    public $pdfReport;

    /**
     * Create a new event instance.
     *
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
