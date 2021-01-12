<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CircleLinkHealth\Customer\Events\PdfableCreated;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateAndHandlePdfReport implements ShouldQueue, ShouldBeEncrypted
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(PdfableCreated $event)
    {
        if ( ! $event->notifyPractice) {
            return false;
        }

        $event->pdfReport->pdfHandleCreated();
    }
}
