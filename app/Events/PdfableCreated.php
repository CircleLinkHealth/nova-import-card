<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use App\Contracts\PdfReport;
use App\Note;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Queue\SerializesModels;

class PdfableCreated extends Event
{
    use SerializesModels;

    /**
     * An entity implementing App\Contracts\PdfReport.
     *
     * @var PdfReport
     */
    public  $pdfReport;
    /**
     * @var bool|mixed
     */
    public $notifyPractice;
    
    /**
     * Create a new event instance.
     *
     * @param PdfReport $pdfable
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
