<?php

namespace App\Listeners;

use App\Events\NoteWasForwarded;

class DispatchNote
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
     * @param  NoteWasForwarded $event
     *
     * @return void
     */
    public function handle(NoteWasForwarded $event)
    {
        $event->note->pdfDispatch();
    }
}
