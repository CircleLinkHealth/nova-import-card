<?php

namespace App\Listeners;

use App\Events\PdfableCreated;

class CreateAndHandlePdfReport
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PdfableCreated $event
     *
     * @return void
     */
    public function handle(PdfableCreated $event)
    {
        $event->pdfReport->pdfHandleCreated();
    }
}
