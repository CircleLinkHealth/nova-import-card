<?php

namespace App\Listeners;

use App\Services\PhiMail\Events\DirectMailMessageReceived;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UPG0506WorkflowListener implements ShouldQueue
{
    use InteractsWithQueue;

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
     * @param  object  $event
     * @return void
     */
    public function handle(DirectMailMessageReceived $event)
    {
        if (! str_contains($event->directMailMessage->from, '@upg.ssdirect.aprima.com')){
            return;
        }
    }
}
