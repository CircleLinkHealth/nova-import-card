<?php

namespace App\Listeners;

use App\DirectMailMessage;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifySlackChannel implements ShouldQueue
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
        if ($event->directMailMessage->num_attachments > 0) {
            $this->notifyAdmins($event->directMailMessage);
        }
    }
    
    /**
     * This is to help notify us of the status of CCDs we receive.
     *
     * @param DirectMailMessage $dm
     */
    private function notifyAdmins(
        DirectMailMessage $dm
    ) {
        if (app()->environment('local')) {
            return;
        }
        
        $link        = route('import.ccd.remix');
        $messageLink = route('direct-mail.show', [$dm->id]);
        
        sendSlackMessage(
            '#ccd-file-status',
            "We received a message from EMR Direct. \n Click here to see the message {$messageLink}. \n If a CCD was included in the message, it has been imported. Click here {$link} to QA and Import."
        );
    }
}
