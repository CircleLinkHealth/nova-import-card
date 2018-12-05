<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Events\PdfableCreated;

class CreateAndHandlePdfReport
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param PdfableCreated $event
     */
    public function handle(PdfableCreated $event)
    {
        if ( ! $event->notifyPractice) {
            return false;
        }

        $event->pdfReport->pdfHandleCreated();
    }
}
