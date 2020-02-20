<?php

namespace App\Listeners;

use App\Services\PhiMail\Events\DirectMailMessageReceived;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChangeOrApproveCareplanResponseListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     *
     * @param  object $event
     *
     * @return void
     */
    public function handle(DirectMailMessageReceived $event)
    {
        if ($this->shouldBail($event->directMailMessage->from)) {
            return;
        }
        if ($this->getApprovalCode($event->directMailMessage->body)) {
        
        }
        if ($this->getChangesCode($event->directMailMessage->body)) {
        
        }
    }
    
    private function shouldBail(string $sender)
    {
        return ! str_contains($sender, '@upg.ssdirect.aprima.com');
    }
    
    public function getChangesCode(string $body)
    {
        return $this->extractCarePlanId($body, '#change');
    }
    
    public function getApprovalCode(string $body)
    {
        return $this->extractCarePlanId($body, '#approve');
    }
    
    private function extractCarePlanId(string $body, string $key) :?int
    {
        preg_match_all("/$key\s*([\d]+)/", $body, $matches);
        
        if (array_key_exists(1, $matches)) {
            return (int) $matches[1][0];
        }
        
        return null;
    }
}
